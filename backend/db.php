<?php

function getDB() {
    $conn = new mysqli('localhost', 'root', 'root');

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Create the database if it doesn't exist
    $db_name = 'voting_system';
    $sql = "CREATE DATABASE IF NOT EXISTS $db_name";
    if (!$conn->query($sql)) {
        die("Error creating database: " . $conn->error);
    }

    // Select the database to use
    $conn->select_db($db_name);

    // Check if the users table exists, if not, create it
    $table_sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    )";
    if (!$conn->query($table_sql)) {
        die("Error creating table: " . $conn->error);
    }

    // Create votes table if not exists
    $table_sql = "CREATE TABLE IF NOT EXISTS votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        candidate_name VARCHAR(255) NOT NULL,
        candidate_id INT NOT NULL,
        vote_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if (!$conn->query($table_sql)) {
        die("Error creating table: " . $conn->error);
    }

    // Add default users if they don't exist
    $default_users = [
        ['username' => 'admin', 'password' => 'admin123'],
        ['username' => 'user1', 'password' => 'pass123'],
        ['username' => 'user2', 'password' => 'pass123'],
        ['username' => 'user3', 'password' => 'pass123']
    ];

    $insert_sql = "INSERT IGNORE INTO users (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($insert_sql);

    foreach ($default_users as $user) {
        // In a production environment, you should hash the password
        // $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);
        $stmt->bind_param("ss", $user['username'], $user['password']);
        $stmt->execute();
    }

    $stmt->close();
    return $conn;
}
?>