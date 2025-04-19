<?php
// Database connection
function getDbConnection() {
    static $conn;
    
    if ($conn === null) {
        try {
            $conn = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    
    return $conn;
}

// Initialize database tables if they don't exist
function initDatabase() {
    $db = getDbConnection();
    
    // Users table
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        balance DECIMAL(10,2) DEFAULT 0.00,
        referral_code VARCHAR(10) NOT NULL UNIQUE,
        referred_by INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (referred_by) REFERENCES users(id) ON DELETE SET NULL
    )");
    
    // Links table
    $db->exec("CREATE TABLE IF NOT EXISTS links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        original_url TEXT NOT NULL,
        short_code VARCHAR(10) NOT NULL UNIQUE,
        title VARCHAR(100) DEFAULT NULL,
        clicks INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    
    // Earnings table
    $db->exec("CREATE TABLE IF NOT EXISTS earnings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        link_id INT NOT NULL,
        amount DECIMAL(10,4) NOT NULL,
        referral_amount DECIMAL(10,4) DEFAULT 0.00,
        referral_to INT DEFAULT NULL,
        ip_address VARCHAR(45) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (link_id) REFERENCES links(id) ON DELETE CASCADE,
        FOREIGN KEY (referral_to) REFERENCES users(id) ON DELETE SET NULL
    )");
    
    // Withdrawals table
    $db->exec("CREATE TABLE IF NOT EXISTS withdrawals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        payment_details TEXT NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        processed_at TIMESTAMP NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
}

// Initialize database on first run
initDatabase();
?>