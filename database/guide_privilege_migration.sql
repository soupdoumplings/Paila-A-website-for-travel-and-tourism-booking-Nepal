USE nepal_tours;

CREATE TABLE IF NOT EXISTS guide_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(30),
    license_no VARCHAR(80),
    languages VARCHAR(255),
    specialties VARCHAR(255),
    experience_years INT DEFAULT 0,
    rating DECIMAL(2,1) DEFAULT 4.8,
    bio TEXT,
    avatar VARCHAR(255),
    created_by INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

SET @db_name = DATABASE();

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE bookings ADD COLUMN phone VARCHAR(30) NULL AFTER contact_email',
        'SELECT 1'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'bookings' AND COLUMN_NAME = 'phone'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE bookings ADD COLUMN special_requests TEXT NULL AFTER travelers',
        'SELECT 1'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'bookings' AND COLUMN_NAME = 'special_requests'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE bookings ADD COLUMN is_premium TINYINT(1) DEFAULT 0 AFTER special_requests',
        'SELECT 1'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'bookings' AND COLUMN_NAME = 'is_premium'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

INSERT INTO roles (id, name, description) VALUES
(4, 'tour_guide', 'Assigned to tours, no admin access')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Optional demo guide profiles. Password for all demo guides: Guide@2026
INSERT INTO users (username, email, password, role_id) VALUES
('mingma_guide', 'mingma@paila.guide', '$2y$10$NLaolsXPbR/dL3MuyO0m8OPOEB49cnTwNOk4/wWm1FrJOeaLjfXki', 4),
('saraswati_guide', 'saraswati@paila.guide', '$2y$10$NLaolsXPbR/dL3MuyO0m8OPOEB49cnTwNOk4/wWm1FrJOeaLjfXki', 4),
('nabin_guide', 'nabin@paila.guide', '$2y$10$NLaolsXPbR/dL3MuyO0m8OPOEB49cnTwNOk4/wWm1FrJOeaLjfXki', 4)
ON DUPLICATE KEY UPDATE role_id = VALUES(role_id);

INSERT INTO guide_profiles (user_id, full_name, phone, license_no, languages, specialties, experience_years, rating, bio, avatar, created_by)
SELECT id, 'Mingma Sherpa', '+977 9800001101', 'NTB-G-8841', 'Nepali, English, Sherpa', 'Everest trails, altitude safety, luxury trekking', 9, 4.9, 'Everest-region guide known for calm pacing, weather judgment, and high-altitude guest care.', 'assets/images/Everest/photo-1544735716-87fa59a45b4e.jpg', 1 FROM users WHERE email = 'mingma@paila.guide'
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), phone = VALUES(phone), license_no = VALUES(license_no), languages = VALUES(languages), specialties = VALUES(specialties), experience_years = VALUES(experience_years), rating = VALUES(rating), bio = VALUES(bio), avatar = VALUES(avatar);

INSERT INTO guide_profiles (user_id, full_name, phone, license_no, languages, specialties, experience_years, rating, bio, avatar, created_by)
SELECT id, 'Saraswati Gurung', '+977 9800001102', 'NTB-G-7712', 'Nepali, English, Gurung, Hindi', 'Culture walks, heritage storytelling, family trips', 7, 4.8, 'Cultural host and city guide focused on heritage routes, temple etiquette, and easy guest communication.', 'assets/images/kathmandu/Swayambhunath_temple_-_an_ancient_religious_architecture_of_Nepal.jpg', 1 FROM users WHERE email = 'saraswati@paila.guide'
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), phone = VALUES(phone), license_no = VALUES(license_no), languages = VALUES(languages), specialties = VALUES(specialties), experience_years = VALUES(experience_years), rating = VALUES(rating), bio = VALUES(bio), avatar = VALUES(avatar);

INSERT INTO guide_profiles (user_id, full_name, phone, license_no, languages, specialties, experience_years, rating, bio, avatar, created_by)
SELECT id, 'Nabin Thapa', '+977 9800001103', 'NTB-G-6904', 'Nepali, English, Hindi', 'Pokhara adventure, Chitwan wildlife, family logistics', 6, 4.7, 'Soft-spoken field guide for mixed adventure itineraries, family pacing, and smooth transfer days.', 'assets/images/Pokhara/pexels-photo-30131353.jpeg', 1 FROM users WHERE email = 'nabin@paila.guide'
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), phone = VALUES(phone), license_no = VALUES(license_no), languages = VALUES(languages), specialties = VALUES(specialties), experience_years = VALUES(experience_years), rating = VALUES(rating), bio = VALUES(bio), avatar = VALUES(avatar);
