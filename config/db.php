<?php
/**
 * Database connection (PDO, prepared-statement friendly)
 * Edit the four constants below to match your local MySQL setup.
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'hospital_management');
define('DB_USER', 'root');
define('DB_PASS', '');       // set your MySQL root password if you have one

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // real prepared statements
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed. Please check config/db.php — ' . htmlspecialchars($e->getMessage()));
}
