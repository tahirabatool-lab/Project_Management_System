-- Project Management System Database Schema
-- Import this file in phpMyAdmin or run via MySQL CLI

CREATE DATABASE IF NOT EXISTS pms;
USE pms;

-- Users Table (both admin and regular users)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);





-- Project Submissions Table
CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    notes TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Default Admin Account (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Administrator', 'admin@pms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Note: Default admin password is 'password' (hashed above)
-- Change this in production!
-- To use admin123, run this instead:
-- INSERT INTO users (name, email, password, role) VALUES
-- ('Administrator', 'admin@pms.com', '$2y$10$YourHashHere', 'admin');
