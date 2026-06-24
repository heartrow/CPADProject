CREATE DATABASE IF NOT EXISTS greenstep_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; 

USE greenstep_db;

DROP TABLE IF EXISTS activity_types; 
CREATE TABLE activity_types (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    category            VARCHAR(50) NOT NULL,
    name                VARCHAR(150) NOT NULL,
    unit                VARCHAR(20) NOT NULL,
    co2_per_unit     DECIMAL(10, 4) NOT NULL,

    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_category (category)

)   ENGINE=InnoDB; 

USE greenstep_db;
INSERT INTO activity_types (category, name, unit, co2_per_unit) VALUES 
    (   
        'meal',  
        'Beef / Lamb (Highest Impact)',  
        'kg',
        35.0000
    ),
    (   
        'meal',  
        'Pork / Poultry (Medium Impact)',  
        'kg',
        8.0000
    ),
    (   
        'meal',  
        'Fish / Seafood (Medium Impact)',  
        'kg',
        9.5000
    ),
    (   
        'meal',  
        'Dairy / Eggs / Vegetarian (Low Impact)',  
        'kg',
        4.2000
    ),
    (   
        'meal',  
        'Plant-based / Vegan (Lowest Impact)',  
        'kg',
        1.4000
    );

INSERT INTO activity_types (category, name, unit, co2_per_unit) VALUES 
    (
        'transport',
        'Private Car (Petrol)',
        'km',
        0.1920
    ),
    (
        'transport',
        'Electric Vehicle (EV)',
        'km',
        0.0530
    ),
    (
        'transport',
        'Public Bus',
        'km',
        0.0820
    ),
    (
        'transport',
        'Train / MRT',
        'km',
        0.0350
    );
