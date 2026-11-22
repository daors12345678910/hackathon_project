
DROP DATABASE IF EXISTS clothesdonation;

CREATE DATABASE clothesdonation
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE clothesdonation;

-- =========================================
-- 1. USERS
-- Only one admin account
-- =========================================

CREATE TABLE users (
    id INT NOT NULL AUTO_INCREMENT,
    emri VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    confirm_password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (username),
    UNIQUE (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- =========================================
-- 2. CHARITY PROFILES
-- Two charities without user accounts
-- =========================================

CREATE TABLE charity_profiles (
    id INT NOT NULL AUTO_INCREMENT,
    charity_name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    city VARCHAR(255) NOT NULL,
    user_id INT DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO charity_profiles
(id, charity_name, description, city, user_id)
VALUES
(
    1,
    'Hope for Everyone',
    'A local charity supporting families and individuals in need of clothing and essential items.',
    'Prishtinë',
    NULL
),
(
    2,
    'Helping Hands Foundation',
    'A community charity collecting clothes and footwear for children and families.',
    'Prizren',
    NULL
);

-- =========================================
-- 3. CLOTHING NEEDED
-- Four requests
-- =========================================

CREATE TABLE clothing_needed (
    request_id INT NOT NULL AUTO_INCREMENT,
    charity_id INT NOT NULL,
    category VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    urgency VARCHAR(50) NOT NULL,
    date DATE NOT NULL,
    image VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    clothing_size VARCHAR(50) NOT NULL,
    PRIMARY KEY (request_id),
    FOREIGN KEY (charity_id)
        REFERENCES charity_profiles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO clothing_needed
(request_id, charity_id, category, description, urgency, date, image, quantity, clothing_size)
VALUES
(
    1,
    1,
    'Clothing',
    'Warm winter jackets needed for families who cannot afford winter clothing.',
    'urgent',
    '2026-11-22',
    'images/images (3).jpg',
    15,
    'M'
),
(
    2,
    1,
    'Footwear',
    'Comfortable shoes needed for children attending school.',
    'medium',
    '2026-11-22',
    'images/images (4).jpg',
    10,
    'S'
),
(
    3,
    2,
    'Clothing',
    'Clean sweaters and warm clothes needed for adults and elderly people.',
    'urgent',
    '2026-11-22',
    'images/images (5).jpg',
    20,
    'L'
),
(
    4,
    2,
    'Accessories',
    'Scarves, gloves and winter hats needed for children.',
    'low',
    '2026-11-22',
    'images/images.jpg',
    12,
    'XS'
);

-- =========================================
-- 4. DONATIONS
-- Empty table: zero donations
-- =========================================

CREATE TABLE donation (
    donation_id INT NOT NULL AUTO_INCREMENT,
    request_id INT NOT NULL,
    donor_id INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    image VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    clothing_size VARCHAR(50) NOT NULL,
    PRIMARY KEY (donation_id),
    FOREIGN KEY (request_id)
        REFERENCES clothing_needed(request_id),
    FOREIGN KEY (donor_id)
        REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- No donation records are inserted.

-- =========================================
-- 5. AUTO-INCREMENT SETTINGS
-- =========================================

ALTER TABLE users AUTO_INCREMENT = 6;
ALTER TABLE charity_profiles AUTO_INCREMENT = 3;
ALTER TABLE clothing_needed AUTO_INCREMENT = 5;
ALTER TABLE donation AUTO_INCREMENT = 1;
