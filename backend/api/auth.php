<?php
// api/auth.php - Authentication endpoints with debugging
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '/home/vivian/Documents/Cytonn/backend/config/database.php';
require_once '/home/vivian/Documents/Cytonn/backend/config/jwt.php';
require_once '/home/vivian/Documents/Cytonn/backend/models/user.php';

// Helper function to send JSON response
function sendResponse($success, $data = null, $message = '', $statusCode = 200) {
    http_response_code($statusCode);
    $response = ['success' => $success];
    if ($data !== null) $response['data'] = $data;
    if ($message !== '') $response['message'] = $message;
    echo json_encode($response);
    exit;
}

// Debug: Log the request
error_log("Auth endpoint called with method: " . $_SERVER['REQUEST_METHOD']);

try {
    $database = new Database();
    $db = $database->connect();
    error_log("Database connected successfully");
    
    $user = new User($db);
    $jwt = new JWTAuth();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST') {
        $input = file_get_contents('php://input');
        error_log("Raw input: " . $input);
        
        $data = json_decode($input, true);
        
        if (!$data) {
            error_log("Invalid JSON data received");
            sendResponse(false, null, 'Invalid JSON data', 400);
        }
        
        error_log("Parsed data: " . print_r($data, true));
        
        // Login endpoint
        if (!isset($data['username']) || !isset($data['password'])) {
            error_log("Missing username or password");
            sendResponse(false, null, 'Username and password are required', 400);
        }
        
        error_log("Attempting login for username: " . $data['username']);
        
        // Call the login method from User class
        $user_data = $user->login($data['username'], $data['password']);
        
        error_log("Login result: " . print_r($user_data, true));
        
        if ($user_data) {
            // Generate JWT token
            $token = $jwt->generateToken($user_data);
            
            // Return user data with token (remove sensitive data)
            unset($user_data['password']); // Don't send password back
            
            error_log("Login successful for user: " . $data['username']);
            sendResponse(true, [
                'user' => $user_data,
                'token' => $token,
                'expires_in' => 3600 // 1 hour
            ], 'Login successful');
        } else {
            error_log("Login failed for user: " . $data['username']);
            sendResponse(false, null, 'Invalid credentials', 401);
        }
    } else {
        error_log("Method not allowed: " . $method);
        sendResponse(false, null, 'Method not allowed', 405);
    }
    
} catch (Exception $e) {
    error_log("Exception in auth.php: " . $e->getMessage());
    sendResponse(false, null, 'Internal server error', 500);
}
?>