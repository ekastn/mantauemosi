-- Create database
CREATE DATABASE IF NOT EXISTS emotion_tracker;
USE emotion_tracker;

-- Create User table
CREATE TABLE IF NOT EXISTS users (
    userID VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('Mahasiswa', 'Dosen') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create EmotionRecord table
CREATE TABLE IF NOT EXISTS emotion_records (
    emotionID VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    userID VARCHAR(36) NOT NULL,
    emotionType ENUM('Senang', 'Sedih', 'Marah', 'Cemas', 'Lelah', 'Bosan', 'Bersemangat', 'Tenang') NOT NULL,
    description TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userID) REFERENCES users(userID) ON DELETE CASCADE
);

-- Create Notification table
CREATE TABLE IF NOT EXISTS notifications (
    notificationID VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    userID VARCHAR(36) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userID) REFERENCES users(userID) ON DELETE CASCADE
);

-- Create ManualRecommendation table
CREATE TABLE IF NOT EXISTS manual_recommendations (
    recommendationID VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    dosenID VARCHAR(36) NOT NULL,
    mahasiswaID VARCHAR(36) NOT NULL,
    emotionID VARCHAR(36) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dosenID) REFERENCES users(userID) ON DELETE CASCADE,
    FOREIGN KEY (mahasiswaID) REFERENCES users(userID) ON DELETE CASCADE,
    FOREIGN KEY (emotionID) REFERENCES emotion_records(emotionID) ON DELETE CASCADE
);

-- Insert sample data
-- Default password for all users: password123
INSERT INTO users (userID, name, email, password, role) VALUES
('mhs1', 'Mahasiswa Satu', 'mhs1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mahasiswa'),
('mhs2', 'Mahasiswa Dua', 'mhs2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mahasiswa'),
('dosen1', 'Dosen Satu', 'dosen1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dosen');

-- Sample emotion records
INSERT INTO emotion_records (emotionID, userID, emotionType, description, timestamp) VALUES
('emo1', 'mhs1', 'Senang', 'Hari ini presentasi berjalan dengan baik', '2025-07-01 09:00:00'),
('emo2', 'mhs1', 'Cemas', 'Besok ada ujian', '2025-07-02 14:30:00'),
('emo3', 'mhs2', 'Lelah', 'Banyak tugas yang harus diselesaikan', '2025-07-02 16:45:00');
