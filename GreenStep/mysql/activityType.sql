CREATE DATABASE IF NOT EXISTS greenstep_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; 

USE greenstep_db;

DROP TABLE IF EXISTS activity_types; 
CREATE TABLE activity_types (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    category            VARCHAR(50) NOT NULL,
    name                VARCHAR(150) NOT NULL,
    unit                VARCHAR(20) NOT NULL,
    kg_co2_per_unit     DECIMAL(10, 4) NOT NULL,

    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_category (category)

)   ENGINE=InnoDB; 

INSERT INTO users (name, email, role, password_hash) VALUES 
    (   'fikri',  
        'fikri@green.com',  
        'member',
        '$2y$10$bo5HrN3QOAq0C6dPrXyIreOqYxoLuMysPLPuLN6WoS2jqrcG4WtVm'
    );