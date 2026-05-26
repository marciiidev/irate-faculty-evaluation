<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'evaluation_db';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure password_text and is_deleted columns exist
$tables = ['users', 'faculty_list', 'student_list'];
foreach ($tables as $table) {
    $check = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'password_text'");
    if ($check && $check->num_rows == 0) {
        $conn->query("ALTER TABLE `$table` ADD COLUMN password_text VARCHAR(255)");
    }
}

$tablesForDelete = ['users', 'faculty_list', 'student_list', 'academic_list', 'class_list', 'subject_list', 'criteria_list', 'question_list', 'restriction_list'];
foreach ($tablesForDelete as $table) {
    $checkDeleted = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'is_deleted'");
    if ($checkDeleted && $checkDeleted->num_rows == 0) {
        $conn->query("ALTER TABLE `$table` ADD COLUMN is_deleted TINYINT(1) DEFAULT 0");
    }
}

$tablesForAvatar = ['users', 'faculty_list', 'student_list'];
foreach ($tablesForAvatar as $table) {
    $checkAvatar = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'avatar'");
    if ($checkAvatar && $checkAvatar->num_rows == 0) {
        $conn->query("ALTER TABLE `$table` ADD COLUMN avatar VARCHAR(255)");
    }
}

// Add publish features
$conn->query("CREATE TABLE IF NOT EXISTS published_results (
  id INT AUTO_INCREMENT PRIMARY KEY,
  academic_id INT,
  faculty_id INT,
  is_published TINYINT(1) DEFAULT 0,
  UNIQUE KEY (academic_id, faculty_id)
)");

$conn->query("CREATE TABLE IF NOT EXISTS evaluation_comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  evaluation_id INT,
  comment TEXT,
  is_published TINYINT(1) DEFAULT 0
)");

$checkPub = $conn->query("SHOW COLUMNS FROM evaluation_comments LIKE 'is_published'");
if ($checkPub && $checkPub->num_rows == 0) {
    $conn->query("ALTER TABLE evaluation_comments ADD COLUMN is_published TINYINT(1) DEFAULT 0");
}
?>
