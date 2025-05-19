-- Drop the database if it exists
DROP DATABASE IF EXISTS IPT;

-- Create the database
CREATE DATABASE IPT;
USE IPT;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create user_profiles table
CREATE TABLE IF NOT EXISTS user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    profile_picture VARCHAR(255),
    bio TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create activity_logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create attendance table
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    time_in DATETIME NOT NULL,
    time_out DATETIME,
    status ENUM('present', 'late', 'absent') DEFAULT 'present',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (user_id, date)
);

-- Insert default admin user
-- Password: admin123 (hashed)
INSERT INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@example.com', '$2y$10$8K1p/a0dR1xqM8K3hQz1eOQZQZQZQZQZQZQZQZQZQZQZQZQZQZQZ', 'admin');

-- Insert default staff user
-- Password: staff123 (hashed)
INSERT INTO users (name, email, password, role) VALUES 
('Staff User', 'staff@example.com', '$2y$10$8K1p/a0dR1xqM8K3hQz1eOQZQZQZQZQZQZQZQZQZQZQZQZQZQZQZ', 'staff');

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_activity_logs_user_id ON activity_logs(user_id);
CREATE INDEX idx_activity_logs_created_at ON activity_logs(created_at);
CREATE INDEX idx_attendance_date ON attendance(date);
CREATE INDEX idx_attendance_user ON attendance(user_id); 