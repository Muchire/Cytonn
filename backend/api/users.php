<?php
// api/users.php - Protected User API endpoints with JWT authorization
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '/home/vivian/Documents/Cytonn/backend/config/database.php';
require_once '/home/vivian/Documents/Cytonn/backend/config/jwt.php';
require_once '/home/vivian/Documents/Cytonn/backend/models/user.php';

$database = new Database();
$db = $database->connect();
$user = new User($db);
$jwt = new JWTAuth();

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

$user_id = null;
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = array_values(array_filter(explode('/', $path))); // Better handling of slashes

// Path should be: ["api", "users.php", "7"] or ["users.php", "7"] depending on your setup
if (count($path_parts) >= 2 && is_numeric(end($path_parts))) {
    $user_id = (int)end($path_parts);
}
// Helper function to send JSON response
function sendResponse($success, $data = null, $message = '', $statusCode = 200) {
    http_response_code($statusCode);
    $response = ['success' => $success];
    if ($data !== null) $response['data'] = $data;
    if ($message !== '') $response['message'] = $message;
    echo json_encode($response);
    exit;
}

// Authenticate user for all requests
$current_user = $jwt->authenticate();
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Path parts: " . print_r($path_parts, true));
error_log("Extracted user_id: " . ($user_id ?? 'null'));
switch($method) {
    case 'GET':
        if ($user_id) {
            // GET /api/users/123 - Get single user
            // Users can only access their own data, admins can access any user's data
            if (!$jwt->canAccessUserData($user_id, $current_user)) {
                sendResponse(false, null, 'Access denied', 403);
            }
            
            $result = $user->getUserById($user_id);
            if ($result) {
                sendResponse(true, $result);
            } else {
                sendResponse(false, null, 'User not found', 404);
            }
        } else {
            // GET /api/users - Get all users (admin only)
            $jwt->authorize(['admin'], $current_user);
            
            $result = $user->getUsers();
            sendResponse(true, $result);
        }
        break;

    case 'POST':
        // POST /api/users - Create new user (admin only)
        $jwt->authorize(['admin'], $current_user);
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            sendResponse(false, null, 'Invalid JSON data', 400);
        }
        
        $required_fields = ['username', 'email', 'full_name', 'password'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                sendResponse(false, null, "Field '$field' is required", 400);
            }
        }
        
        try {
            $result = $user->createUser(
                $data['username'],
                $data['email'],
                $data['full_name'],
                $data['password'],
                $data['role'] ?? 'student'
            );
            
            if ($result) {
                sendResponse(true, ['id' => $result], 'User created successfully', 201);
            } else {
                sendResponse(false, null, 'Failed to create user', 500);
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                if (strpos($e->getMessage(), 'username') !== false) {
                    sendResponse(false, null, 'Username already exists', 409);
                } elseif (strpos($e->getMessage(), 'email') !== false) {
                    sendResponse(false, null, 'Email already exists', 409);
                } else {
                    sendResponse(false, null, 'Duplicate entry', 409);
                }
            } else {
                sendResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
            }
        }
        break;

    case 'PUT':
        if (!$user_id) {
            sendResponse(false, null, 'User ID is required in URL', 400);
        }
        
        // Users can only update their own data, admins can update any user's data
        if (!$jwt->canAccessUserData($user_id, $current_user)) {
            sendResponse(false, null, 'Access denied', 403);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            sendResponse(false, null, 'Invalid JSON data', 400);
        }
        
        // Check if user exists
        $existing_user = $user->getUserById($user_id);
        if (!$existing_user) {
            sendResponse(false, null, 'User not found', 404);
        }
        
        // Check if this is a password update
        if (isset($data['current_password']) && isset($data['new_password'])) {
            // Password update - users can only update their own password
            if ($current_user['user_id'] != $user_id) {
                sendResponse(false, null, 'You can only update your own password', 403);
            }
            
            $result = $user->updatePassword($user_id, $data['current_password'], $data['new_password']);
            
            if ($result) {
                sendResponse(true, null, 'Password updated successfully');
            } else {
                sendResponse(false, null, 'Current password is incorrect', 400);
            }
        } else {
            // Regular user update
            $required_fields = ['username', 'email', 'full_name'];
            
            // Only admins can update user roles
            if ($current_user['role'] === 'admin') {
                $required_fields[] = 'role';
            } else {
                // For non-admin users, use the current role
                $data['role'] = $existing_user['role'];
            }
            
            foreach ($required_fields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    sendResponse(false, null, "Field '$field' is required", 400);
                }
            }
            
            try {
                $result = $user->updateUser(
                    $user_id,
                    $data['username'],
                    $data['email'],
                    $data['full_name'],
                    $data['role']
                );
                
                if ($result) {
                    sendResponse(true, null, 'User updated successfully');
                } else {
                    sendResponse(false, null, 'Failed to update user', 500);
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    if (strpos($e->getMessage(), 'username') !== false) {
                        sendResponse(false, null, 'Username already exists', 409);
                    } elseif (strpos($e->getMessage(), 'email') !== false) {
                        sendResponse(false, null, 'Email already exists', 409);
                    } else {
                        sendResponse(false, null, 'Duplicate entry', 409);
                    }
                } else {
                    sendResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
                }
            }
        }
        break;
    case 'DELETE':
        if (!$user_id) {
            sendResponse(false, null, 'User ID is required in URL', 400);
        }
        
        try {
            // Authenticate
            $current_user = $jwt->authenticate();
            
            // Authorize (admin only)
            $jwt->authorize(['admin'], $current_user);
            
            // Check if user exists
            if (!$user->getUserById($user_id)) {
                sendResponse(false, null, 'User not found', 404);
            }
            
            // Prevent self-deletion
            if ($current_user['user_id'] == $user_id) {
                sendResponse(false, null, 'You cannot delete your own account', 400);
            }
            
            // Perform deletion
            if ($user->deleteUser($user_id)) {
                sendResponse(true, null, 'User deleted successfully');
            } else {
                sendResponse(false, null, 'Failed to delete user', 500);
            }
            
        } catch (Exception $e) {
            sendResponse(false, null, $e->getMessage(), 401);
        }
        break;
    default:
        sendResponse(false, null, 'Method not allowed', 405);
        break;
}
?>