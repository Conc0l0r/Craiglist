-- Job Board Database Schema for MySQL

CREATE DATABASE IF NOT EXISTS jobfinding;
USE jobfinding;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    google_id VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    username VARCHAR(100) UNIQUE NULL,
    picture TEXT,
    degree VARCHAR(100) NULL,
    university VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- IT Jobs Table
CREATE TABLE IF NOT EXISTS jobs_it (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company VARCHAR(255) NOT NULL,
    post VARCHAR(255) NOT NULL,
    department VARCHAR(255) NOT NULL,
    salary VARCHAR(255) NOT NULL,
    requirements TEXT NOT NULL,
    vacancy_from DATE NOT NULL,
    contact_email VARCHAR(255) NOT NULL,
    is_vacant TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Marketing Jobs Table
CREATE TABLE IF NOT EXISTS jobs_marketing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company VARCHAR(255) NOT NULL,
    post VARCHAR(255) NOT NULL,
    department VARCHAR(255) NOT NULL,
    salary VARCHAR(255) NOT NULL,
    requirements TEXT NOT NULL,
    vacancy_from DATE NOT NULL,
    contact_email VARCHAR(255) NOT NULL,
    is_vacant TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Accounting Jobs Table
CREATE TABLE IF NOT EXISTS jobs_accounting (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company VARCHAR(255) NOT NULL,
    post VARCHAR(255) NOT NULL,
    department VARCHAR(255) NOT NULL,
    salary VARCHAR(255) NOT NULL,
    requirements TEXT NOT NULL,
    vacancy_from DATE NOT NULL,
    contact_email VARCHAR(255) NOT NULL,
    is_vacant TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Medical Jobs Table
CREATE TABLE IF NOT EXISTS jobs_medical (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company VARCHAR(255) NOT NULL,
    post VARCHAR(255) NOT NULL,
    department VARCHAR(255) NOT NULL,
    salary VARCHAR(255) NOT NULL,
    requirements TEXT NOT NULL,
    vacancy_from DATE NOT NULL,
    contact_email VARCHAR(255) NOT NULL,
    is_vacant TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
