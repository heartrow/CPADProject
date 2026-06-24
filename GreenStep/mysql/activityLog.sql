CREATE DATABASE IF NOT EXISTS greenstep_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; 

USE greenstep_db;

DROP TABLE IF EXISTS activity_logs; 
CREATE TABLE activity_logs (
    id                  INT AUTO_INCREMENT PRIMARY KEY, 
    user_id             INT NOT NULL, 
    activity_type_id    INT NOT NULL, 
    title					VARCHAR(200) NOT NULL DEFAULT 'Untitled',
    amount              DECIMAL(10, 2) NOT NULL, 
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_log_user 
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
    
    CONSTRAINT fk_log_type 
    FOREIGN KEY (activity_type_id) REFERENCES activity_types(id)
    ON DELETE CASCADE 
    ON UPDATE CASCADE

)   ENGINE=INNODB; 

INSERT INTO activity_logs (user_id, activity_type_id, title, amount) VALUES 
    (   '',  
        '',  
        '',
        ''
    );