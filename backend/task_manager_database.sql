-- Task Manager Database SQL Dump
-- Database: MySQL
-- Version: 5.7+ or 8.0+

CREATE DATABASE IF NOT EXISTS task_manager;

USE task_manager;

-- Table structure for table `users`
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table structure for table `tasks`
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    assigned_to INT,
    deadline DATETIME,
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert sample admin user
-- Password is 'password' (hashed with PHP password_hash())
INSERT INTO users (username, email, full_name, password, role) VALUES 
('admin', 'admin@school.com', 'Administrator', 'admin12345', 'admin');

-- Insert sample student users
INSERT INTO users (username, email, full_name, password, role) VALUES 
('John', 'john@school.com', 'John Doe', 'john12345', 'student'),
('Jane', 'jane@school.com', 'Jane Smith', 'jane12345', 'student'),
('Bob', 'bob@school.com', 'Bob Johnson', 'bob12345', 'student');

-- Insert sample tasks
INSERT INTO tasks (title, description, assigned_to, deadline, created_by, status) VALUES 
('Complete Math Assignment', 'Solve problems 1-20 from Chapter 5', 2, '2024-01-15 23:59:59', 1, 'pending'),
('Science Project Research', 'Research and prepare presentation on renewable energy', 3, '2024-01-20 23:59:59', 1, 'in_progress'),
('Literature Essay', 'Write 500-word essay on Romeo and Juliet', 4, '2024-01-18 23:59:59', 1, 'pending'),
('History Timeline', 'Create timeline of World War II events', 2, '2024-01-25 23:59:59', 1, 'completed');

-- Create indexes for better performance
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_tasks_assigned_to ON tasks(assigned_to);
CREATE INDEX idx_tasks_created_by ON tasks(created_by);
CREATE INDEX idx_tasks_status ON tasks(status);
CREATE INDEX idx_tasks_deadline ON tasks(deadline);