CREATE DATABASE IF NOT EXISTS foredog_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE foredog_db;

DROP TABLE IF EXISTS survey_sessions;
DROP TABLE IF EXISTS scrape_log;
DROP TABLE IF EXISTS dogs;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    name               VARCHAR(100) NOT NULL,
    email              VARCHAR(150) UNIQUE NOT NULL,
    phone              VARCHAR(20) NOT NULL,
    is_subscribed      BOOLEAN DEFAULT FALSE,
    stripe_customer_id VARCHAR(100) DEFAULT NULL,
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE dogs (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    external_id         VARCHAR(255) UNIQUE,
    source_shelter      VARCHAR(150),
    source_url          VARCHAR(255),
    source_state        VARCHAR(10)  DEFAULT NULL,
    name                VARCHAR(100) NOT NULL,
    breed_slug          VARCHAR(100) NOT NULL,
    breed_name          VARCHAR(100) NOT NULL,
    location            VARCHAR(255),
    city                VARCHAR(100) DEFAULT NULL,
    state               VARCHAR(10)  DEFAULT NULL,
    latitude            DECIMAL(10,7) DEFAULT NULL,
    longitude           DECIMAL(10,7) DEFAULT NULL,
    age                 VARCHAR(50),
    gender              ENUM('Male','Female','Unknown') DEFAULT 'Unknown',
    color               VARCHAR(50),
    description         TEXT,
    image_url           VARCHAR(255),
    gallery_urls        TEXT,
    owner_contact_name  VARCHAR(100),
    owner_contact_phone VARCHAR(20),
    owner_contact_email VARCHAR(150),
    status              ENUM('available','adopted') DEFAULT 'available',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_seen_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    adopted_at          TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_breed_status (breed_slug, status),
    INDEX idx_state_status (state, status),
    INDEX idx_status (status)
);

CREATE TABLE survey_sessions (
    id                     INT AUTO_INCREMENT PRIMARY KEY,
    session_id             VARCHAR(255) UNIQUE NOT NULL,
    user_id                INT DEFAULT NULL,
    recommended_breed_slug VARCHAR(100) NOT NULL,
    completed_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE scrape_log (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    shelter_name VARCHAR(100),
    shelter_url  VARCHAR(255),
    state        VARCHAR(10) DEFAULT NULL,
    dogs_found   INT DEFAULT 0,
    dogs_added   INT DEFAULT 0,
    dogs_updated INT DEFAULT 0,
    status       ENUM('success','error','partial'),
    error_msg    TEXT,
    duration_sec DECIMAL(5,2),
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);