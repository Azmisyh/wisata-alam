-- Database Schema for Indonesian Tourist Destination Recommendation System
-- Created for UAS Web Programming Project

-- Create Database
CREATE DATABASE IF NOT EXISTS wisata_alam;
USE wisata_alam;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user', 'banned') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Destinations Table
CREATE TABLE destinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    location VARCHAR(255),
    province VARCHAR(100),
    category ENUM('alam', 'budaya', 'sejarah', 'rekreasi') DEFAULT 'alam',
    image_url VARCHAR(255),
    rating_avg DECIMAL(3,2) DEFAULT 0.00,
    review_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Reviews Table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destination_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sessions Table for session management
CREATE TABLE sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload TEXT NOT NULL,
    last_activity INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample admin user (password: admin123)
INSERT INTO users (username, password, email, full_name, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@wisataalam.com', 'Administrator', 'admin');

-- Insert sample destinations
INSERT INTO destinations (name, description, location, province, category, image_url) VALUES 
('Raja Ampat', 'Kepulauan dengan keindahan bawah laut yang menakjubkan, surga bagi penyelam dan pecinta alam.', 'Papua Barat', 'Papua Barat', 'alam', 'https://images.unsplash.com/photo-1540202404-1b927e35f35b?w=800'),
('Borobudur', 'Candi Buddha terbesar di dunia, warisan budaya UNESCO dengan arsitektur yang megah.', 'Magelang', 'Jawa Tengah', 'budaya', 'https://images.unsplash.com/photo-1545558014-8692077e9b5c?w=800'),
('Pulau Komodo', 'Habitat asli komodo, kadal terbesar di dunia, dengan pantai dan diving yang spektakuler.', 'Nusa Tenggara Timur', 'Nusa Tenggara Timur', 'alam', 'https://images.unsplash.com/photo-154290916831-9d516783cdf2?w=800'),
('Tana Toraja', 'Daerah pegunungan dengan budaya unik, rumah adat Tongkonan, dan ritual pemakaman khas.', 'Sulawesi Selatan', 'Sulawesi Selatan', 'budaya', 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800'),
('Danau Toba', 'Danau vulkanik terbesar di dunia, dengan Pulau Samosir di tengahnya.', 'Sumatera Utara', 'Sumatera Utara', 'alam', 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800'),
('Pantai Kuta', 'Pantai terkenal di Bali dengan matahari terbenam yang indah dan ombak untuk berselancar.', 'Bali', 'Bali', 'rekreasi', 'https://images.unsplash.com/photo-1537953773345-d172ccf13cf1?w=800');
