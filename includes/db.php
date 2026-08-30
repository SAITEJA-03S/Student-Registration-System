<?php
/**
 * Database Connection & Auto Initialization
 * Dual Engine: MySQL (with auto creation) & SQLite (zero-config fallback)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('MYSQL_HOST', '127.0.0.1');
define('MYSQL_PORT', '3306');
define('MYSQL_DB', 'student_reg_db');
define('MYSQL_USER', 'root');
define('MYSQL_PASS', '');

$db_file = __DIR__ . '/../database/student_system.sqlite';

$pdo = null;
$db_driver = 'sqlite';

try {
    // Quick port check first so we don't stall if MySQL service is not started
    $socket = @fsockopen(MYSQL_HOST, MYSQL_PORT, $errno, $errstr, 0.4);
    if ($socket) {
        fclose($socket);
        // Attempt MySQL connection
        $mysql_dsn = "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";charset=utf8mb4";
        $test_pdo = new PDO($mysql_dsn, MYSQL_USER, MYSQL_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // Auto-create database if not exists
        $test_pdo->exec("CREATE DATABASE IF NOT EXISTS `" . MYSQL_DB . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $test_pdo->exec("USE `" . MYSQL_DB . "`");
        $pdo = $test_pdo;
        $db_driver = 'mysql';
    } else {
        throw new Exception("MySQL server not reachable on port 3306");
    }
} catch (Exception $e) {
    // Seamlessly fallback to SQLite
    $db_dir = __DIR__ . '/../database';
    if (!is_dir($db_dir)) {
        mkdir($db_dir, 0777, true);
    }
    $pdo = new PDO("sqlite:" . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db_driver = 'sqlite';
}

// Table schema initializer
function init_database($pdo, $driver) {
    if ($driver === 'mysql') {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(20) NOT NULL DEFAULT 'admin',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS students (
                id INT AUTO_INCREMENT PRIMARY KEY,
                fullname VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                mobile VARCHAR(20) NOT NULL,
                dob DATE NOT NULL,
                gender VARCHAR(10) NOT NULL,
                course VARCHAR(50) NOT NULL,
                semester VARCHAR(20) NOT NULL,
                hobbies TEXT,
                address TEXT NOT NULL,
                photo VARCHAR(255) DEFAULT 'default_avatar.svg',
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS reports (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT,
                report_type VARCHAR(50) NOT NULL,
                file_path VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
            ) ENGINE=InnoDB;
        ");
    } else {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT 'admin',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS students (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                fullname TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                mobile TEXT NOT NULL,
                dob TEXT NOT NULL,
                gender TEXT NOT NULL,
                course TEXT NOT NULL,
                semester TEXT NOT NULL,
                hobbies TEXT,
                address TEXT NOT NULL,
                photo TEXT DEFAULT 'default_avatar.svg',
                password TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS reports (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                student_id INTEGER,
                report_type TEXT NOT NULL,
                file_path TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
            );
        ");
    }

    // Seed default admin user (admin / admin123)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $hashed_pass = password_hash('admin123', PASSWORD_DEFAULT);
        $insert_admin = $pdo->prepare("INSERT INTO users (username, password, role) VALUES ('admin', ?, 'admin')");
        $insert_admin->execute([$hashed_pass]);
    }

    // Seed sample students if empty
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM students");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $dummy_pass = password_hash('student123', PASSWORD_DEFAULT);
        $dummy_students = [
            ['Rahul Kumar', 'rahul@example.com', '9876543210', '2003-05-22', 'Male', 'BCA', 'Semester 5', 'Reading, Sports', '123, New Colony, Bhopal, Madhya Pradesh', 'default_avatar.svg', $dummy_pass],
            ['Priya Sharma', 'priya@example.com', '9822334455', '2002-11-14', 'Female', 'BBA', 'Semester 3', 'Music, Reading', '45 Park Avenue, Indore, MP', 'default_avatar.svg', $dummy_pass],
            ['Aman Verma', 'aman@example.com', '9687452012', '2001-08-19', 'Male', 'MCA', 'Semester 2', 'Sports, Coding', '78 Sector 9, Gwalior, MP', 'default_avatar.svg', $dummy_pass],
            ['Neha Singh', 'neha@example.com', '9712345678', '2003-02-10', 'Female', 'BCA', 'Semester 4', 'Music, Other', '89 Lake View Road, Jabalpur, MP', 'default_avatar.svg', $dummy_pass],
            ['Vikram Patel', 'vikram@example.com', '9898989898', '2002-04-15', 'Male', 'B.Tech (CSE)', 'Semester 6', 'Reading, Coding', '12 Tech Hub, Bhopal, MP', 'default_avatar.svg', $dummy_pass],
            ['Ananya Roy', 'ananya@example.com', '9777888999', '2004-09-25', 'Female', 'BBA', 'Semester 1', 'Reading, Music', '55 Green Meadows, Ujjain, MP', 'default_avatar.svg', $dummy_pass]
        ];

        $ins_sql = "INSERT INTO students (fullname, email, mobile, dob, gender, course, semester, hobbies, address, photo, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $ins_stmt = $pdo->prepare($ins_sql);
        foreach ($dummy_students as $st) {
            $ins_stmt->execute($st);
        }
    }
}

init_database($pdo, $db_driver);
