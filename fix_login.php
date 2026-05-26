<?php
require_once 'evaluation_db/db_connect.php';

echo "<h1>Login Fix Tool</h1>";

if ($conn->connect_error) {
    die("<p style='color:red'>Connection failed: " . $conn->connect_error . "</p>");
}

echo "<p style='color:green'>Database connected successfully!</p>";

// The hash for the word 'password'
$password_hash = password_hash('password', PASSWORD_DEFAULT);

// 1. Reset SuperAdmin01
$check = $conn->query("SELECT * FROM users WHERE email = 'SuperAdmin01'");
if ($check->num_rows > 0) {
    $conn->query("UPDATE users SET password = '$password_hash' WHERE email = 'SuperAdmin01'");
    echo "<p>SuperAdmin01 password has been reset to 'password'.</p>";
} else {
    $conn->query("INSERT INTO users (firstname, lastname, email, password, role) VALUES ('Super', 'Admin', 'SuperAdmin01', '$password_hash', 'superadmin')");
    echo "<p>SuperAdmin01 account created with password 'password'.</p>";
}

// 2. Reset Admin01
$check = $conn->query("SELECT * FROM users WHERE email = 'Admin01'");
if ($check->num_rows > 0) {
    $conn->query("UPDATE users SET password = '$password_hash' WHERE email = 'Admin01'");
    echo "<p>Admin01 password has been reset to 'password'.</p>";
} else {
    $conn->query("INSERT INTO users (firstname, lastname, email, password, role) VALUES ('Admin', 'User', 'Admin01', '$password_hash', 'admin')");
    echo "<p>Admin01 account created with password 'password'.</p>";
}

echo "<br><a href='index.php'>Go to Login Page</a>";
?>
