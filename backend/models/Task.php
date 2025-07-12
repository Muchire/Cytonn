<?php
// models/Task.php - Task model
class Task {
    private $conn;
    private $table = 'tasks';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all tasks with user info
    public function getTasks() {
        $query = "SELECT t.*, u.username, u.full_name, u.email 
                  FROM " . $this->table . " t 
                  LEFT JOIN users u ON t.assigned_to = u.id 
                  ORDER BY t.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get tasks by user ID
    public function getTasksByUser($user_id) {
        $query = "SELECT t.*, u.username, u.full_name 
                  FROM " . $this->table . " t 
                  LEFT JOIN users u ON t.assigned_to = u.id 
                  WHERE t.assigned_to = ? 
                  ORDER BY t.deadline ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create new task
    public function createTask($title, $description, $assigned_to, $deadline, $created_by) {
        $query = "INSERT INTO " . $this->table . " (title, description, assigned_to, deadline, created_by, status) VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = $this->conn->prepare($query);
        
        if($stmt->execute([$title, $description, $assigned_to, $deadline, $created_by])) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Update task
    public function updateTask($id, $title, $description, $assigned_to, $deadline, $status) {
        $query = "UPDATE " . $this->table . " SET title = ?, description = ?, assigned_to = ?, deadline = ?, status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$title, $description, $assigned_to, $deadline, $status, $id]);
    }

    // Update task status
    public function updateTaskStatus($id, $status) {
        $query = "UPDATE " . $this->table . " SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$status, $id]);
    }

    // Delete task
    public function deleteTask($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    // Get task by ID
    public function getTaskById($id) {
        $query = "SELECT t.*, u.username, u.full_name, u.email 
                  FROM " . $this->table . " t 
                  LEFT JOIN users u ON t.assigned_to = u.id 
                  WHERE t.id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>