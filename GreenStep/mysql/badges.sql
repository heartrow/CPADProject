CREATE DATABASE IF NOT EXISTS greenstep_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE greenstep_db;

-- ============================================================
-- TABLE: badges
-- Stores badge definitions and the criteria required to earn them.
-- criteriaJSON holds flexible rule data, e.g.:
--   {"type": "total_co2_saved", "threshold": 100}
-- ============================================================
DROP TABLE IF EXISTS user_badges;
DROP TABLE IF EXISTS badges;

CREATE TABLE badges (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(150) NOT NULL,
    criteria_json   JSON         NOT NULL,
    image_url       VARCHAR(500) NOT NULL,

    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

) ENGINE=InnoDB;

-- ============================================================
-- TABLE: user_badges  (junction table — User EARN Badge)
-- Records which user earned which badge and when.
-- A user can earn many badges; a badge can be earned by many users.
-- ============================================================
CREATE TABLE user_badges (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT      NOT NULL,
    badge_id        INT      NOT NULL,
    earned_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_ub_user
        FOREIGN KEY (user_id)  REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_ub_badge
        FOREIGN KEY (badge_id) REFERENCES badges(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    -- Prevent the same badge from being awarded to a user twice
    UNIQUE KEY uq_user_badge (user_id, badge_id)

) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA: badges
-- ============================================================
INSERT INTO badges (name, criteria_json, image_url) VALUES
    (
        'First Step',
        '{"type": "total_logs", "threshold": 1}',
        'https://greenstep.app/badges/first-step.png'
    ),
    (
        'Green Beginner',
        '{"type": "total_co2_saved_kg", "threshold": 10}',
        'https://greenstep.app/badges/green-beginner.png'
    ),
    (
        'Eco Warrior',
        '{"type": "total_co2_saved_kg", "threshold": 100}',
        'https://greenstep.app/badges/eco-warrior.png'
    ),
    (
        'Plant-Based Pioneer',
        '{"type": "activity_category_streak", "category": "meal", "activity_type_id": 5, "days": 7}',
        'https://greenstep.app/badges/plant-based-pioneer.png'
    ),
    (
        'Public Transport Champion',
        '{"type": "activity_category_logs", "category": "transport", "activity_type_ids": [8, 9], "threshold": 20}',
        'https://greenstep.app/badges/transport-champion.png'
    );

-- ============================================================
-- SEED DATA: user_badges
-- Example — award "First Step" badge to Fikri (user_id = 1)
-- ============================================================
INSERT INTO user_badges (user_id, badge_id) VALUES
    (1, 1);