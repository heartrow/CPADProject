USE greenstep_db;

DROP TABLE IF EXISTS challenges;
CREATE TABLE challenges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    targetGoal INT NOT NULL,
    unit VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE challenges
    ADD COLUMN activity_type_id INT NULL AFTER unit,
    ADD COLUMN start_date DATE NULL AFTER activity_type_id,
    ADD COLUMN end_date DATE NULL AFTER start_date,
    ADD CONSTRAINT fk_challenges_activity_type
        FOREIGN KEY (activity_type_id) REFERENCES activity_types(id)
        ON DELETE SET NULL;