<?php
// api/users.php - User API endpoints
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';
require_once '../models/User.php';

$database = new Database();
$db = $database->connect();
$user = new User($db);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            // Get single user
            $result = $user->getUserById($_GET['id']);
            if($result) {
                echo json_encode(['success' => true, 'data' => $result]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'User not found']);
            }
        } else {
            // Get all users
            $result = $user->getUsers();
            echo json_encode(['success' => true, 'data' => $result]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if(isset($data['action']) && $data['action'] === 'login') {
            // Login
            $result = $user->login($data['username'], $data['password']);
            if($result) {
                echo json_encode(['success' => true, 'data' => $result, 'message' => 'Login successful']);
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            }
        } else {
            // Create user
            $result = $user->createUser(
                $data['username'],
                $data['email'],
                $data['full_name'],
                $data['password'],
                $data['role'] ?? 'student'
            );
            
            if($result) {
                echo json_encode(['success' => true, 'data' => ['id' => $result], 'message' => 'User created successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create user']);
            }
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        
        $result = $user->updateUser(
            $id,
            $data['username'],
            $data['email'],
            $data['full_name'],
            $data['role']
        );
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update user']);
        }
        break;

    case 'DELETE':
        $id = $_GET['id'];
        $result = $user->deleteUser($id);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
        }
        break;
}
?>