USE greenstep_db;

DROP TABLE IF EXISTS user_challenges;
CREATE TABLE user_challenges (
    user_id INT NOT NULL,          
    challenge_id INT NOT NULL,     
    contribution INT DEFAULT 0,    
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, challenge_id)
);