-- Database structure for E-Sim Store Admin Panel
-- This SQL file contains the necessary tables for the admin panel

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS esim_store;

-- Use the database
USE esim_store;

-- Create admin_users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT,
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user
-- Default credentials: username: admin, password: admin123
-- Note: In production, you should change these credentials immediately
INSERT INTO admin_users (username, password, email) 
VALUES ('admin', '$2y$10$8WxmVFW.dgjXH7QNpIuRZOWVJ/3KvkXMQUHb9LJf8Xm1.QHCQtk4e', 'admin@example.com')
ON DUPLICATE KEY UPDATE id = id;

-- Insert sample products (optional)
INSERT INTO products (name, stock, price, description, image_path) VALUES
('E-Sim Basic', 100, 99000, 'Paket data 5GB untuk 7 hari', 'uploads/sample1.jpg'),
('E-Sim Premium', 50, 199000, 'Paket data 15GB untuk 15 hari', 'uploads/sample2.jpg'),
('E-Sim Ultimate', 25, 299000, 'Paket data 30GB untuk 30 hari', 'uploads/sample3.jpg')
ON DUPLICATE KEY UPDATE id = id;
