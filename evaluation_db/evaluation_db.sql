CREATE DATABASE IF NOT EXISTS evaluation_db;
USE evaluation_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  firstname VARCHAR(255),
  lastname VARCHAR(255),
  email VARCHAR(255) UNIQUE,
  password VARCHAR(255),
  role ENUM('superadmin', 'admin') DEFAULT 'admin',
  avatar VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS academic_list (
  id INT AUTO_INCREMENT PRIMARY KEY,
  year VARCHAR(255),
  semester INT,
  is_default TINYINT(1) DEFAULT 0,
  status INT DEFAULT 0 -- 0: Pending, 1: Started, 2: Closed
);

CREATE TABLE IF NOT EXISTS class_list (
  id INT AUTO_INCREMENT PRIMARY KEY,
  class_name VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS subject_list (
  id INT AUTO_INCREMENT PRIMARY KEY,
  subject_code VARCHAR(255),
  subject_name VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS faculty_list (
  id INT AUTO_INCREMENT PRIMARY KEY,
  school_id VARCHAR(255) UNIQUE,
  firstname VARCHAR(255),
  lastname VARCHAR(255),
  email VARCHAR(255) UNIQUE,
  password VARCHAR(255),
  avatar VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS student_list (
  id INT AUTO_INCREMENT PRIMARY KEY,
  school_id VARCHAR(255) UNIQUE,
  firstname VARCHAR(255),
  lastname VARCHAR(255),
  email VARCHAR(255) UNIQUE,
  password VARCHAR(255),
  class_id INT,
  avatar VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS criteria_list (
  id INT AUTO_INCREMENT PRIMARY KEY,
  criteria VARCHAR(255),
  order_by INT
);

CREATE TABLE IF NOT EXISTS question_list (
  id INT AUTO_INCREMENT PRIMARY KEY,
  academic_id INT,
  criteria_id INT,
  question TEXT,
  order_by INT
);

CREATE TABLE IF NOT EXISTS evaluation_list (
  id INT AUTO_INCREMENT PRIMARY KEY,
  academic_id INT,
  student_id INT,
  faculty_id INT,
  class_id INT,
  subject_id INT,
  date_created DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS evaluation_answers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  evaluation_id INT,
  question_id INT,
  rating INT
);

CREATE TABLE IF NOT EXISTS published_results (
  id INT AUTO_INCREMENT PRIMARY KEY,
  academic_id INT,
  faculty_id INT,
  is_published TINYINT(1) DEFAULT 0,
  UNIQUE KEY (academic_id, faculty_id)
);

CREATE TABLE IF NOT EXISTS evaluation_comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  evaluation_id INT,
  comment TEXT,
  is_published TINYINT(1) DEFAULT 0
);

CREATE TABLE IF NOT EXISTS restriction_list (
  id INT AUTO_INCREMENT PRIMARY KEY,
  academic_id INT,
  faculty_id INT,
  class_id INT,
  subject_id INT
);

-- Seed initial users (All passwords are 'password')
TRUNCATE TABLE users;
TRUNCATE TABLE faculty_list;
TRUNCATE TABLE student_list;
TRUNCATE TABLE academic_list;

-- SuperAdmin01 / password
INSERT INTO users (firstname, lastname, email, password, role) VALUES 
('Super', 'Admin', 'SuperAdmin01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');

-- Admin01 / password
INSERT INTO users (firstname, lastname, email, password, role) VALUES 
('Admin', 'User', 'Admin01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Faculty01 / password
INSERT INTO faculty_list (school_id, firstname, lastname, email, password) VALUES 
('FAC-001', 'John', 'Faculty', 'Faculty01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Student01 / password
INSERT INTO student_list (school_id, firstname, lastname, email, password) VALUES 
('STU-001', 'Jane', 'Student', 'Student01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO academic_list (year, semester, is_default, status) VALUES 
('2025-2026', 1, 1, 1);
