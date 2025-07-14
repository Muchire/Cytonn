<?php
// api/tasks.php - Protected Task API endpoints with JWT authorization
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '/home/vivian/Documents/Cytonn/backend/config/database.php';
require_once '/home/vivian/Documents/Cytonn/backend/config/jwt.php';
require_once '/home/vivian/Documents/Cytonn/backend/config/email.php';
require_once '/home/vivian/Documents/Cytonn/backend/models/Task.php';
require_once '/home/vivian/Documents/Cytonn/backend/models/user.php';

$database = new Database();
$db = $database->connect();
$task = new Task($db);
$user = new User($db);
$jwt = new JWTAuth();
$emailService = new EmailService();

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));
$task_id = null;

if (count($path_parts) >= 3 && $path_parts[1] === 'tasks.php' && is_numeric($path_parts[2])) {
    $task_id = (int)$path_parts[2];
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

switch($method) {
    case 'GET':
        if ($task_id) {
            // GET /api/tasks.php?task_id=123 - Fetch a single task
            $result = $task->getTaskById($task_id);
            if (!$result) {
                sendResponse(false, null, 'Task not found', 404);
            }

            // Access control: only admin or task owner/creator can view
            if (
                $current_user['role'] !== 'admin' &&
                $result['assigned_to'] != $current_user['user_id'] &&
                $result['created_by'] != $current_user['user_id']
            ) {
                sendResponse(false, null, 'Access denied', 403);
            }

            sendResponse(true, $result);
        } else {
            // GET /api/tasks.php - Fetch all tasks
            if ($current_user['role'] === 'admin') {
                $result = isset($_GET['user_id']) 
                    ? $task->getTasksByUser($_GET['user_id']) 
                    : $task->getTasks();
            } else {
                $result = $task->getTasksByUser($current_user['user_id']);
            }

            sendResponse(true, $result);
        }
        break;
    case 'POST':
        // POST /api/tasks - Create new task (admin only)
        $jwt->authorize(['admin'], $current_user);
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            sendResponse(false, null, 'Invalid JSON data', 400);
        }
        
        $required_fields = ['title', 'description', 'assigned_to', 'deadline'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                sendResponse(false, null, "Field '$field' is required", 400);
            }
        }
        
        // Validate deadline format
        if (!strtotime($data['deadline'])) {
            sendResponse(false, null, 'Invalid deadline format', 400);
        }
        
        // Check if assigned user exists
        $assignedUser = $user->getUserById($data['assigned_to']);
        if (!$assignedUser) {
            sendResponse(false, null, 'Assigned user not found', 404);
        }
        
        try {
            $result = $task->createTask(
                $data['title'],
                $data['description'],
                $data['assigned_to'],
                $data['deadline'],
                $current_user['user_id'] // Use current user as creator
            );
            
            if ($result) {
                // Send email notification
                $subject = "New Task Assigned: " . $data['title'];
                $body = "
                    <h2>New Task Assigned</h2>
                    <p><strong>Task:</strong> " . $data['title'] . "</p>
                    <p><strong>Description:</strong> " . $data['description'] . "</p>
                    <p><strong>Deadline:</strong> " . date('F j, Y g:i A', strtotime($data['deadline'])) . "</p>
                    <p>Please log in to the system to view and update your task status.</p>
                ";
                
                try {
                    $emailService->sendEmail(
                        $assignedUser['email'],
                        $assignedUser['full_name'],
                        $subject,
                        $body
                    );
                } catch (Exception $e) {
                    error_log("Email notification failed: " . $e->getMessage());
                }
                
                sendResponse(true, ['id' => $result], 'Task created successfully', 201);
            } else {
                sendResponse(false, null, 'Failed to create task', 500);
            }
        } catch (Exception $e) {
            sendResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
        }
        break;

    case 'PUT':
        if (!$task_id) {
            sendResponse(false, null, 'Task ID is required in URL', 400);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            sendResponse(false, null, 'Invalid JSON data', 400);
        }
        
        // Check if task exists
        $existing_task = $task->getTaskById($task_id);
        if (!$existing_task) {
            sendResponse(false, null, 'Task not found', 404);
        }
        
        try {
            if (isset($data['status_only']) && $data['status_only']) {
                // PUT /api/tasks/123 with status_only=true - Update only status
                // Users can only update status of their own tasks
                if ($current_user['role'] !== 'admin' && 
                    $existing_task['assigned_to'] != $current_user['user_id']) {
                    sendResponse(false, null, 'You can only update your own task status', 403);
                }
                
                if (!isset($data['status'])) {
                    sendResponse(false, null, 'Status is required', 400);
                }
                
                $valid_statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
                if (!in_array($data['status'], $valid_statuses)) {
                    sendResponse(false, null, 'Invalid status. Valid statuses: ' . implode(', ', $valid_statuses), 400);
                }
                
                $result = $task->updateTaskStatus($task_id, $data['status']);
            } else {
                // PUT /api/tasks/123 - Update full task (admin only)
                $jwt->authorize(['admin'], $current_user);
                
                $required_fields = ['title', 'description', 'assigned_to', 'deadline', 'status'];
                foreach ($required_fields as $field) {
                    if (!isset($data[$field]) || empty($data[$field])) {
                        sendResponse(false, null, "Field '$field' is required", 400);
                    }
                }
                
                // Validate deadline format
                if (!strtotime($data['deadline'])) {
                    sendResponse(false, null, 'Invalid deadline format', 400);
                }
                
                // Check if assigned user exists
                $assignedUser = $user->getUserById($data['assigned_to']);
                if (!$assignedUser) {
                    sendResponse(false, null, 'Assigned user not found', 404);
                }
                
                $valid_statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
                if (!in_array($data['status'], $valid_statuses)) {
                    sendResponse(false, null, 'Invalid status. Valid statuses: ' . implode(', ', $valid_statuses), 400);
                }
                
                $result = $task->updateTask(
                    $task_id,
                    $data['title'],
                    $data['description'],
                    $data['assigned_to'],
                    $data['deadline'],
                    $data['status']
                );
            }
            
            if ($result) {
                sendResponse(true, null, 'Task updated successfully');
            } else {
                sendResponse(false, null, 'Failed to update task', 500);
            }
        } catch (Exception $e) {
            sendResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
        }
        break;

    case 'DELETE':
        if (!$task_id) {
            sendResponse(false, null, 'Task ID is required in URL', 400);
        }
        
        // Only admins can delete tasks
        $jwt->authorize(['admin'], $current_user);
        
        // Check if task exists
        $existing_task = $task->getTaskById($task_id);
        if (!$existing_task) {
            sendResponse(false, null, 'Task not found', 404);
        }
        
        try {
            $result = $task->deleteTask($task_id);
            
            if ($result) {
                sendResponse(true, null, 'Task deleted successfully');
            } else {
                sendResponse(false, null, 'Failed to delete task', 500);
            }
        } catch (Exception $e) {
            sendResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
        }
        break;

    default:
        sendResponse(false, null, 'Method not allowed', 405);
        break;
}
?>