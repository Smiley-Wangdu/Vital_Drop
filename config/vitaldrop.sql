-- VitalDrop Database Schema
-- Run this in phpMyAdmin or MySQL CLI to set up the database

CREATE DATABASE IF NOT EXISTS vitaldrop;
USE vitaldrop;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    age INT NOT NULL,
    blood_group VARCHAR(5) NOT NULL,
    location VARCHAR(200) NOT NULL,
    health_notes TEXT DEFAULT NULL,
    role VARCHAR(20) DEFAULT 'user',
    is_donor TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    age INT DEFAULT NULL,
    blood_group VARCHAR(5) DEFAULT NULL,
    location VARCHAR(200) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user
-- Password: admin123
INSERT IGNORE INTO admins (name, email, password, age, blood_group, location) 
VALUES ('Super Admin', 'admin@gmail.com', '$2y$10$ak0G1RjLO9DInwZMArHkn.z7qM9BcFf5rTca3YBDWuV5VGhkHmf4i', 30, 'O+', 'Headquarters');


CREATE TABLE IF NOT EXISTS blood_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    hospital_name VARCHAR(255) NOT NULL,
    blood_group VARCHAR(5) NOT NULL,
    location VARCHAR(255) NOT NULL,
    contact_number VARCHAR(50) NOT NULL,
    units_required INT UNSIGNED NOT NULL DEFAULT 1,
    urgency ENUM('Normal', 'Urgent') DEFAULT 'Normal',
    status ENUM('Active', 'Fulfilled', 'Cancelled', 'Expired') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL DEFAULT NULL
);
