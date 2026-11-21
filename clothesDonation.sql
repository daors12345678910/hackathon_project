
CREATE DATABASE IF NOT EXISTS clothesdonation;
USE clothesdonation;

-- 1. CHARITY PROFILES
CREATE TABLE charity_profiles (
    id INT NOT NULL AUTO_INCREMENT,
    charity_name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    city VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO charity_profiles
(id, charity_name, description, city) VALUES
(1, 'shawn jones', 'Needs all types of clothing', 'Pristina'),
(2, 'drilon hajrizi', 'Needs all types of clothing', 'Prizren'),
(3, 'mohammed salah', 'Needs footwear', 'Peja');


-- 2. USERS
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

INSERT INTO users
(id, emri, username, email, password, confirm_password, role) VALUES
(1, 'test', 'test', 'test@gmail.com',
'$2y$10$0VBVvKNyxIl9OA00/FRmrep7atIIE/SoLXGEoXcjNmF.4ZV6b3NIG',
'$2y$10$0VBVvKNyxIl9OA00/FRmrep7atIIE/SoLXGEoXcjNmF.4ZV6b3NIG', 'charity'),

(2, 'test1', 'test1', 'test1@test1.com',
'$2y$10$erk7uFn0RpqhC6g3VX.XcuyU3CVkOanelghahzqjmdtxeFwfEgQQO',
'$2y$10$2XuH6X04NLc/jwMhciX8OOccIfFHom7oi6o2PSh82Z8BnINw.nu2O', 'admin'),

(3, 'test2', 'test2', 'linda@linda.com',
'$2y$10$pzATPYrl/LDDmFp8DIxalOrvUNT2RL4.I3pFbRjGrsUE8L/xH9O32',
'$2y$10$q.HH1oolBta2lTtF/uCIGu2lwLhoHvKoWljOfqV1TAB5ER44EAQUG', 'donor');


-- 3. CLOTHING NEEDED
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
    FOREIGN KEY (charity_id) REFERENCES charity_profiles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO clothing_needed
(request_id, charity_id, category, description, urgency, date, image, quantity, clothing_size) VALUES
(1, 1, 'footwear', 'shoes', 'urgent', '2026-02-01', 'images/images (3).jpg', 6, 'XS'),
(2, 2, 'hats', 'beanie', 'urgent', '2026-02-01', 'images/images (4).jpg', 7, 'X'),
(3, 3, 'accessory', 'earring', 'urgent', '2026-02-01', 'images/images (5).jpg', 8, 'S');


-- 4. DONATION
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
    FOREIGN KEY (request_id) REFERENCES clothing_needed(request_id),
    FOREIGN KEY (donor_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO donation
(donation_id, request_id, donor_id, description, date, image, quantity, clothing_size) VALUES
(1, 1, 3, 'hats', '2026-02-01', 'images/images (1).jpg', 5, 'XS'),
(2, 1, 3, 'clothes', '2026-02-01', 'images/images (2).jpg', 6, 'S'),
(3, 2, 3, 'footwear', '2026-02-01', 'images/images.jpg', 7, 'X');
