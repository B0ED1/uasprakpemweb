-- SQL Database Setup for FiguSphere
-- UAS Web Programming Project (Updated with User Authentication)

-- Create Database if not exists
CREATE DATABASE IF NOT EXISTS db_figusphere;
USE db_figusphere;

-- 1. Create Table for Users
CREATE TABLE IF NOT EXISTS tb_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Create Table for Action Figures (Linked to User)
CREATE TABLE IF NOT EXISTS tb_figures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama_figure VARCHAR(255) NOT NULL,
    karakter VARCHAR(100) NOT NULL,
    seri_anime VARCHAR(150) NOT NULL,
    produsen VARCHAR(100) NOT NULL,
    skala_ukuran VARCHAR(50),
    harga INT,
    foto_figure VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES tb_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: Insert dummy users (password: admin123)
-- INSERT INTO tb_users (username, password, nama_lengkap) VALUES 
-- ('admin', '$2y$10$pLw2p.99P0qJ8bsp0WepQ.D8UjZ.vFexEAGkFh5cQf1x861Zz2x8W', 'Administrator FiguSphere');
