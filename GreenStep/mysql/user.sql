CREATE DATABASE IF NOT EXISTS greenstep_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; 

USE greenstep_db;

DROP TABLE IF EXISTS users; 
CREATE TABLE users (
    id              INT AUTO_INCREMENT PRIMARY KEY, 
    name            VARCHAR(150) NOT NULL, 
    email           VARCHAR(190) NOT NULL UNIQUE, 
    role            ENUM('member', 'leader', 'admin') NOT NULL DEFAULT 'member', 
    password_hash   VARCHAR(255) NOT NULL, 
    location        VARCHAR(150) NULL DEFAULT 'Johor, Malaysia',
    program         VARCHAR(150) NULL DEFAULT 'Software Engineering Student',
    avatar          VARCHAR(50) NULL DEFAULT '👨‍💻',
    carbon_factor   VARCHAR(100) NULL DEFAULT 'Standard MY Baseline',
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

)   ENGINE=InnoDB; 

INSERT INTO users (name, email, role, password_hash, location, program, avatar, carbon_factor) VALUES 
    (   'fikri',  
        'fikri@green.com',  
        'member',
        '$2y$10$bo5HrN3QOAq0C6dPrXyIreOqYxoLuMysPLPuLN6WoS2jqrcG4WtVm',
        'Johor, Malaysia',
        'Software Engineering Student',
        '👨‍💻',
        'Standard MY Baseline'
    ), 
    (   'azri',  
        'azri@green.com',  
        'leader',
        '$2y$10$ZOb5FFRah22HG3281GLUzunJw7ZzLT3stl32r3.wo0ma/ThZjgCKC',
        'Johor, Malaysia',
        'Computer Science Student',
        '🌱',
        'Standard MY Baseline'
    ), 
    (   'hasif',  
        'hasif@green.com',  
        'admin',
        '$2y$10$bMP2JOlDBJnyLk47GeMcbe4V5XREQ5N1z1ElAK1uIgDsjYcAxYHBK',
        'Johor, Malaysia',
        'Information Systems Student',
        '⚡',
        'Standard MY Baseline'
    );