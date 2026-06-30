CREATE DATABASE IF NOT EXISTS railway
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; 

USE railway;

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

USE railway;
INSERT INTO activity_types (category, name, unit, co2_per_unit) VALUES 
    (   
        'meal',  
        'Beef / Lamb (Highest Impact)',  
        'kg',
        0.8800  -- long braising/stewing ~1.5 kWh/kg
    ),
    (   
        'meal',  
        'Pork / Poultry (Medium Impact)',  
        'kg',
        0.4700  -- frying/grilling ~0.8 kWh/kg
    ),
    (   
        'meal',  
        'Fish / Seafood (Medium Impact)',  
        'kg',
        0.2900  -- pan-frying ~0.5 kWh/kg
    ),
    (   
        'meal',  
        'Dairy / Eggs / Vegetarian (Low Impact)',  
        'kg',
        0.2300  -- stir-fry ~0.4 kWh/kg
    ),
    (   
        'meal',  
        'Plant-based / Vegan (Lowest Impact)',  
        'kg',
        0.1800  -- light cooking ~0.3 kWh/kg
    );

INSERT INTO activity_types (category, name, unit, co2_per_unit) VALUES 
    (
        'transport',
        'Private Car (Petrol)',
        'km',
        0.2100  -- ~210g/km, typical Malaysian sedan
    ),
    (
        'transport',
        'Electric Vehicle (EV)',
        'km',
        0.0990  -- 0.18 kWh/km × 0.551 kg CO2/kWh (Malaysia grid)
    ),
    (
        'transport',
        'Public Bus',
        'km',
        0.0190  -- per passenger km, assuming ~30 passengers
    ),
    (
        'transport',
        'Train / MRT',
        'km',
        0.0300  -- mixed grid electric rail, per passenger km
    );

INSERT INTO activity_types (category, name, unit, co2_per_unit) VALUES 
    (
        'energy',
        'Air Conditioning',
        'hour',
        1.1100  -- ~1.5 kWh/hour × 0.740 kg CO2/kWh (typical 1.5HP unit)
    ),
    (
        'energy',
        'Computer / Laptop',
        'hour',
        0.0740  -- ~0.1 kWh/hour × 0.740 kg CO2/kWh
    ),
    (
        'energy',
        'Lighting',
        'hour',
        0.0148  -- ~0.02 kWh/hour × 0.740 kg CO2/kWh (LED bulb)
    ),
    (
        'energy',
        'Washing Machine',
        'hour',
        0.5920  -- ~0.8 kWh/hour × 0.740 kg CO2/kWh
    ),

INSERT INTO activity_types (category, name, unit, co2_per_unit) VALUES 
    (
        'recycle',
        'Plastic Bottles / Containers',
        'kg',
        0.0800
    ),
    (
        'recycle',
        'Paper / Cardboard',
        'kg',
        0.0410
    ),
    (
        'recycle',
        'Glass',
        'kg',
        0.0540
    ),
    (
        'recycle',
        'Aluminum / Metal Cans',
        'kg',
        0.5700
    ),
    (
        'recycle',
        'E-Waste (Electronics)',
        'kg',
        0.2300
    );




