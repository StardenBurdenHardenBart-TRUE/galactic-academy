-- ============================================
-- GALACTIC ACADEMY - Database Schema
-- ============================================

CREATE DATABASE IF NOT EXISTS galactic_academy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE galactic_academy;

-- Tabel 1: Users (untuk login admin)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel 2: Mahasiswa
CREATE TABLE IF NOT EXISTS mahasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    jurusan_id INT,
    angkatan YEAR NOT NULL,
    status ENUM('aktif', 'cuti', 'lulus', 'dropout') DEFAULT 'aktif',
    ipk DECIMAL(3,2) DEFAULT 0.00,
    foto VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel 3: Jurusan
CREATE TABLE IF NOT EXISTS jurusan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(10) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    fakultas VARCHAR(100) NOT NULL,
    kapasitas INT DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Foreign Key
ALTER TABLE mahasiswa ADD CONSTRAINT fk_jurusan FOREIGN KEY (jurusan_id) REFERENCES jurusan(id) ON DELETE SET NULL;

-- ============================================
-- DATA AWAL
-- ============================================

INSERT INTO users (username, password, nama_lengkap, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin'),
('staff1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff Akademik', 'staff');

-- Note: Password default = "password" (hash bcrypt Laravel default)
-- Untuk demo, gunakan password: admin123
-- Hash berikut untuk "admin123":
UPDATE users SET password = '$2y$10$YourHashHere' WHERE username = 'admin';


DELETE FROM users;
INSERT INTO users (username, password, nama_lengkap, role) VALUES
('admin', '$2y$10$8K1p/a0dR1xqM8K3Dcnt9ONYnkGDXJtZ5p2Y4yZ4w4oWtPFNJb4Qy', 'Commander Admin', 'admin'),
('staff1', '$2y$10$8K1p/a0dR1xqM8K3Dcnt9ONYnkGDXJtZ5p2Y4yZ4w4oWtPFNJb4Qy', 'Staff Akademik', 'staff');
-- Password untuk keduanya: admin123

INSERT INTO jurusan (kode, nama, fakultas, kapasitas) VALUES
('TI', 'Teknik Informatika', 'Fakultas Teknik', 120),
('SI', 'Sistem Informasi', 'Fakultas Teknik', 100),
('MI', 'Manajemen Informatika', 'Fakultas Ekonomi', 80),
('TK', 'Teknik Komputer', 'Fakultas Teknik', 90),
('BD', 'Big Data Analytics', 'Fakultas Sains', 60);

INSERT INTO mahasiswa (nim, nama, email, jurusan_id, angkatan, status, ipk) VALUES
('2021001', 'Andromeda Putra', 'andromeda@galaxy.ac.id', 1, 2021, 'aktif', 3.75),
('2021002', 'Nova Sari Dewi', 'nova@galaxy.ac.id', 2, 2021, 'aktif', 3.82),
('2022001', 'Orion Kusuma', 'orion@galaxy.ac.id', 1, 2022, 'aktif', 3.50),
('2022002', 'Stella Ramadhani', 'stella@galaxy.ac.id', 3, 2022, 'aktif', 3.90),
('2022003', 'Cosmo Hidayat', 'cosmo@galaxy.ac.id', 4, 2022, 'cuti', 2.80),
('2023001', 'Aurora Pratiwi', 'aurora@galaxy.ac.id', 5, 2023, 'aktif', 3.65),
('2023002', 'Nebula Santoso', 'nebula@galaxy.ac.id', 2, 2023, 'aktif', 3.40),
('2023003', 'Vega Permata', 'vega@galaxy.ac.id', 1, 2023, 'aktif', 3.88),
('2020001', 'Lyra Sukmawati', 'lyra@galaxy.ac.id', 3, 2020, 'lulus', 3.70),
('2020002', 'Sirius Wibowo', 'sirius@galaxy.ac.id', 1, 2020, 'lulus', 3.55);