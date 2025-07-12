<?php
// api/tasks.php - Task API endpoints
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';
require_once '../config/email.php';
require_once '../models/Task.php';
require_once '../models/User.php';

$database = new Database();
$db = $database->connect();
$task = new Task($db);
$user = new User($db);
$emailService = new EmailService();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            // Get single task
            $result = $task->getTaskById($_GET['id']);
            if($result) {
                echo json_encode(['success' => true, 'data' => $result]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Task not found']);
            }
        } else if(isset($_GET['user_id'])) {
            // Get tasks for specific user
            $result = $task->getTasksByUser($_GET['user_id']);
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            // Get all tasks
            $result = $task->getTasks();
            echo json_encode(['success' => true, 'data' => $result]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        $result = $task->createTask(
            $data['title'],
            $data['description'],
            $data['assigned_to'],
            $data['deadline'],
            $data['created_by']
        );
        
        if($result) {
            // Send email notification
            $assignedUser = $user->getUserById($data['assigned_to']);
            if($assignedUser) {
                $subject = "New Task Assigned: " . $data['title'];
                $body = "
                    <h2>New Task Assigned</h2>
                    <p><strong>Task:</strong> " . $data['title'] . "</p>
                    <p><strong>Description:</strong> " . $data['description'] . "</p>
                    <p><strong>Deadline:</strong> " . date('F j, Y g:i A', strtotime($data['deadline'])) . "</p>
                    <p>Please log in to the system to view and update your task status.</p>
                ";
                
                $emailService->sendEmail(
                    $assignedUser['email'],
                    $assignedUser['full_name'],
                    $subject,
                    $body
                );
            }
            
            echo json_encode(['success' => true, 'data' => ['id' => $result], 'message' => 'Task created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create task']);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if(isset($data['status_only']) && $data['status_only']) {
            // Update only status
            $result = $task->updateTaskStatus($data['id'], $data['status']);
        } else {
            // Update full task
            $result = $task->updateTask(
                $data['id'],
                $data['title'],
                $data['description'],
                $data['assigned_to'],
                $data['deadline'],
                $data['status']
            );
        }
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Task updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update task']);
        }
        break;

    case 'DELETE':
        $id = $_GET['id'];
        $result = $task->deleteTask($id);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Task deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete task']);
        }
        break;
}
?>