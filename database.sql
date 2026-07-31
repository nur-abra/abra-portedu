-- Portfolio Management System Database
-- Database: portfolio_system

CREATE DATABASE IF NOT EXISTS portfolio_system
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE portfolio_system;

-- 1. Users (Admin authentication)
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'viewer') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Portfolio Images
CREATE TABLE IF NOT EXISTS portfolio_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    category ENUM('Projects', 'Certificates', 'Achievements', 'Personal Gallery') NOT NULL DEFAULT 'Personal Gallery',
    uploaded_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. About Me
CREATE TABLE IF NOT EXISTS about (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    description TEXT,
    skills TEXT,
    education TEXT,
    experience TEXT,
    achievements TEXT
) ENGINE=InnoDB;

-- 4. Projects (Portfolio projects)
CREATE TABLE IF NOT EXISTS projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    technologies VARCHAR(255),
    project_link VARCHAR(255),
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. Contact Information
CREATE TABLE IF NOT EXISTS contact (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(30),
    email VARCHAR(100),
    facebook VARCHAR(255),
    messenger VARCHAR(255),
    linkedin VARCHAR(255),
    github VARCHAR(255),
    twitter VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 6. Comments
CREATE TABLE IF NOT EXISTS comments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    visitor_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    comment TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 7. Reactions
CREATE TABLE IF NOT EXISTS reactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    visitor_identifier VARCHAR(64) NOT NULL,
    reaction_type ENUM('like', 'love', 'helpful') NOT NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_visitor_reaction (visitor_identifier, reaction_type)
) ENGINE=InnoDB;

-- 8. Feedback
CREATE TABLE IF NOT EXISTS feedback (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    rating TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 9. Password Reset Tokens
CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- Default admin user (password: Admin@123)
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@portfolio.com', '$2y$10$aiW2bqPPG8Lo5eR/tBSpk.EuQDyyv3vTtE0WtFI1EiXDRizzL45Ne', 'admin');

-- Default about content
INSERT INTO about (fullname, description, skills, education, experience, achievements) VALUES
('John Doe', 'Passionate full-stack developer with expertise in building modern web applications.', 'PHP, JavaScript, MySQL, HTML5, CSS3, Bootstrap, Git', 'Bachelor of Science in Computer Science - University Example (2018-2022)', 'Senior Web Developer at Tech Corp (2022-Present)\nJunior Developer at StartUp Inc (2020-2022)', 'Best Developer Award 2023\nOpen Source Contributor');

-- Default contact information
INSERT INTO contact (phone, email, facebook, messenger, linkedin, github, twitter) VALUES
('+1 234 567 8900', 'john.doe@email.com', 'https://facebook.com/johndoe', 'https://m.me/johndoe', 'https://linkedin.com/in/johndoe', 'https://github.com/johndoe', 'https://twitter.com/johndoe');

-- Sample projects
INSERT INTO projects (title, description, technologies, project_link, image_path) VALUES
('Portfolio Website', 'A responsive personal portfolio built with PHP and MySQL.', 'PHP, MySQL, Bootstrap 5', 'https://github.com/johndoe/portfolio', NULL),
('E-Commerce Platform', 'Full-featured online store with payment integration.', 'PHP, JavaScript, Stripe API', 'https://github.com/johndoe/ecommerce', NULL);
