-- Create roles table
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT
);

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL DEFAULT 3, -- Default to 'user'
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Create guide_profiles table
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

-- Create tours table
CREATE TABLE IF NOT EXISTS tours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration VARCHAR(50) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    difficulty VARCHAR(50),
    max_group INT,
    highlights TEXT,
    image VARCHAR(255),
    best_season VARCHAR(50),
    altitude_max INT,
    permit_requirements TEXT,
    itinerary TEXT,
    inclusions TEXT,
    exclusions TEXT,
    is_featured TINYINT(1) DEFAULT 0,
    created_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tour_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    customer_name VARCHAR(100) NOT NULL,
    contact_email VARCHAR(100) NOT NULL,
    phone VARCHAR(30),
    travel_date DATE NOT NULL,
    travelers INT NOT NULL,
    special_requests TEXT,
    is_premium TINYINT(1) DEFAULT 0,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    tour_guide_id INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tour_id) REFERENCES tours(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (tour_guide_id) REFERENCES users(id)
);

-- Create notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    link VARCHAR(255),
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create private_requests table
CREATE TABLE IF NOT EXISTS private_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    details TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    access_code VARCHAR(20) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_access_code (access_code)
);

-- Create messages table
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    context_type ENUM('booking', 'private_request') NOT NULL,
    context_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create inquiries table
CREATE TABLE IF NOT EXISTS inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Base roles used by the application.
INSERT INTO roles (id, name, description) VALUES
(1, 'super_admin', 'Has full access to all settings and can manage other admins'),
(2, 'admin', 'Can manage tours, bookings, guides, and requests'),
(3, 'user', 'Regular customer account'),
(4, 'tour_guide', 'Assigned to bookings, no admin access')
ON DUPLICATE KEY UPDATE
name = VALUES(name),
description = VALUES(description);

-- Local development super admin.
-- Username: ujShresthadmin
-- Email: 2461787@paila.admin
-- Password: PailaAdmin@2026
INSERT INTO users (username, email, password, role_id) VALUES
('ujShresthadmin', '2461787@paila.admin', '$2y$10$E4PhScu8N3V56oulHcbxKuWkqbqg8hdr9V.XahdhtloMd.z43Y62O', 1)
ON DUPLICATE KEY UPDATE
username = VALUES(username),
password = VALUES(password),
role_id = VALUES(role_id);

SET @paila_admin_id := (
    SELECT id
    FROM users
    WHERE email = '2461787@paila.admin'
    LIMIT 1
);

-- Demo guide accounts for booking assignment tests.
-- Password for all demo guides: Guide@2026
INSERT INTO users (username, email, password, role_id) VALUES
('mingma_guide', 'mingma@paila.guide', '$2y$10$NLaolsXPbR/dL3MuyO0m8OPOEB49cnTwNOk4/wWm1FrJOeaLjfXki', 4),
('saraswati_guide', 'saraswati@paila.guide', '$2y$10$NLaolsXPbR/dL3MuyO0m8OPOEB49cnTwNOk4/wWm1FrJOeaLjfXki', 4),
('nabin_guide', 'nabin@paila.guide', '$2y$10$NLaolsXPbR/dL3MuyO0m8OPOEB49cnTwNOk4/wWm1FrJOeaLjfXki', 4)
ON DUPLICATE KEY UPDATE
password = VALUES(password),
role_id = VALUES(role_id);

INSERT INTO guide_profiles (user_id, full_name, phone, license_no, languages, specialties, experience_years, rating, bio, avatar, created_by)
SELECT id, 'Mingma Sherpa', '+977 9800001101', 'NTB-G-8841', 'Nepali, English, Sherpa', 'Everest trails, altitude safety, luxury trekking', 9, 4.9, 'Everest-region guide known for calm pacing, weather judgment, and high-altitude guest care.', 'assets/images/Everest/photo-1544735716-87fa59a45b4e.jpg', @paila_admin_id
FROM users
WHERE email = 'mingma@paila.guide'
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), phone = VALUES(phone), license_no = VALUES(license_no), languages = VALUES(languages), specialties = VALUES(specialties), experience_years = VALUES(experience_years), rating = VALUES(rating), bio = VALUES(bio), avatar = VALUES(avatar), created_by = VALUES(created_by);

INSERT INTO guide_profiles (user_id, full_name, phone, license_no, languages, specialties, experience_years, rating, bio, avatar, created_by)
SELECT id, 'Saraswati Gurung', '+977 9800001102', 'NTB-G-7712', 'Nepali, English, Gurung, Hindi', 'Culture walks, heritage storytelling, family trips', 7, 4.8, 'Cultural host and city guide focused on heritage routes, temple etiquette, and easy guest communication.', 'assets/images/kathmandu/Swayambhunath_temple_-_an_ancient_religious_architecture_of_Nepal.jpg', @paila_admin_id
FROM users
WHERE email = 'saraswati@paila.guide'
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), phone = VALUES(phone), license_no = VALUES(license_no), languages = VALUES(languages), specialties = VALUES(specialties), experience_years = VALUES(experience_years), rating = VALUES(rating), bio = VALUES(bio), avatar = VALUES(avatar), created_by = VALUES(created_by);

INSERT INTO guide_profiles (user_id, full_name, phone, license_no, languages, specialties, experience_years, rating, bio, avatar, created_by)
SELECT id, 'Nabin Thapa', '+977 9800001103', 'NTB-G-6904', 'Nepali, English, Hindi', 'Pokhara adventure, Chitwan wildlife, family logistics', 6, 4.7, 'Soft-spoken field guide for mixed adventure itineraries, family pacing, and smooth transfer days.', 'assets/images/Pokhara/pexels-photo-30131353.jpeg', @paila_admin_id
FROM users
WHERE email = 'nabin@paila.guide'
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), phone = VALUES(phone), license_no = VALUES(license_no), languages = VALUES(languages), specialties = VALUES(specialties), experience_years = VALUES(experience_years), rating = VALUES(rating), bio = VALUES(bio), avatar = VALUES(avatar), created_by = VALUES(created_by);
