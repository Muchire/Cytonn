<?php
// models/User.php - User model
class User {
    private $conn;
    private $table = 'users';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all users
    public function getUsers() {
        $query = "SELECT id, username, email, full_name, role, created_at FROM " . $this->table . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get user by ID
    public function getUserById($id) {
        $query = "SELECT id, username, email, full_name, role, created_at FROM " . $this->table . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create new user
    public function createUser($username, $email, $full_name, $password, $role = 'student') {
        $query = "INSERT INTO " . $this->table . " (username, email, full_name, password, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        if($stmt->execute([$username, $email, $full_name, $hashed_password, $role])) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Update user
    public function updateUser($id, $username, $email, $full_name, $role) {
        $query = "UPDATE " . $this->table . " SET username = ?, email = ?, full_name = ?, role = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$username, $email, $full_name, $role, $id]);
    }

    // Delete user
    public function deleteUser($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    // Login user
    public function login($username, $password) {
        $query = "SELECT id, username, email, full_name, password, role FROM " . $this->table . " WHERE username = ? OR email = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }
        return false;
    }
}
?>