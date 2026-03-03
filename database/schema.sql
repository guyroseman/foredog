CREATE DATABASE IF NOT EXISTS foredog_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE foredog_db;

CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) UNIQUE NOT NULL,
    phone         VARCHAR(20) NOT NULL,
    is_subscribed BOOLEAN DEFAULT FALSE,
    stripe_customer_id VARCHAR(100) DEFAULT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS dogs (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    name                VARCHAR(100) NOT NULL,
    breed_slug          VARCHAR(100) NOT NULL,
    breed_name          VARCHAR(100) NOT NULL,
    location            VARCHAR(255),
    age                 VARCHAR(50),
    gender              ENUM('Male', 'Female', 'Unknown') DEFAULT 'Unknown',
    color               VARCHAR(50),
    description         TEXT,
    image_url           VARCHAR(255),
    owner_contact_name  VARCHAR(100),
    owner_contact_phone VARCHAR(20),
    owner_contact_email VARCHAR(150),
    status              ENUM('available', 'adopted') DEFAULT 'available',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS survey_sessions (
    id                     INT AUTO_INCREMENT PRIMARY KEY,
    session_id             VARCHAR(255) UNIQUE NOT NULL,
    user_id                INT DEFAULT NULL,
    recommended_breed_slug VARCHAR(100) NOT NULL,
    completed_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO dogs (name, breed_slug, breed_name, location, age, gender, color, description, image_url, owner_contact_name, owner_contact_phone, owner_contact_email) VALUES
('Jake',  'german-shepherd',    'German Shepherd',   'Austin, TX',    '3 years',  'Male',   'Black & Tan', 'Jake is a loyal, energetic German Shepherd who loves long runs, learning new tricks, and cuddles on the couch. Great with older kids. Fully vaccinated and neutered.',          'https://images.unsplash.com/photo-1589941013453-ec89f33b5e95?w=600&q=80',  'Austin Rescue Center',     '(512) 555-0101', 'intake@austinrescue.org'),
('Luna',  'golden-retriever',   'Golden Retriever',  'Denver, CO',    '2 years',  'Female', 'Golden',      'Luna is pure sunshine. Gets along with everyone. Completed basic obedience training and ready for her forever home. Spayed and up to date on all shots.',                    'https://images.unsplash.com/photo-1612774412771-005ed8e861d2?w=600&q=80',  'Denver Animal Shelter',    '(303) 555-0188', 'adopt@denvershelter.org'),
('Max',   'labrador-retriever', 'Labrador Retriever','Chicago, IL',   '4 years',  'Male',   'Chocolate',   'Max is a gentle giant with a heart of gold. Loves fetch and swimming. Great with families. House-trained and crate-trained.',                                               'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=600&q=80',  'Chicago Labs Rescue',      '(312) 555-0142', 'hello@chicagolabs.org'),
('Bella', 'french-bulldog',     'French Bulldog',    'Miami, FL',     '1 year',   'Female', 'Brindle',     'Bella is a sassy, loveable Frenchie who thinks she runs the house. Perfect for apartment living. Comes with all vet records.',                                              'https://images.unsplash.com/photo-1583511655826-05700d52f4d9?w=600&q=80',  'Miami Frenchie Rescue',    '(305) 555-0177', 'contact@miamifrenchie.com'),
('Rocky', 'german-shepherd',    'German Shepherd',   'Seattle, WA',   '5 years',  'Male',   'Sable',       'Rocky is a calm, confident shepherd with a background in therapy work. Excellent manners. Suitable for experienced dog owners. Neutered and microchipped.',                 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?w=600&q=80',  'Seattle Shepherd Rescue',  '(206) 555-0133', 'rocky@seattleshepherds.org'),
('Daisy', 'golden-retriever',   'Golden Retriever',  'Portland, OR',  '6 years',  'Female', 'Light Gold',  'Daisy is a senior golden with so much love left to give. Calm, gentle, house-trained. Ideal for a quiet household that wants a devoted companion.',                        'https://images.unsplash.com/photo-1552053831-71594a27632d?w=600&q=80',  'Portland Golden Hearts',   '(503) 555-0199', 'daisy@goldenheartspdx.org');
