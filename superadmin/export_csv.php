<?php
session_start();
if (!isset($_SESSION['login_id']) || !in_array($_SESSION['login_type'], ['superadmin', 'admin'])) {
    header("location: ../index.php");
    exit();
}
require_once '../evaluation_db/db_connect.php';

$type = $_GET['type'] ?? '';
$academic_id = $_GET['academic_id'] ?? 0;

if ($type == 'student') {
    $filename = "students_list_" . date('Y-m-d') . ".csv";
    $query = $conn->query("SELECT s.school_id, s.firstname, s.lastname, s.email, s.password_text, c.class_name FROM student_list s LEFT JOIN class_list c ON s.class_id = c.id ORDER BY s.lastname ASC");
    $header = array("School ID", "First Name", "Last Name", "Email", "Password", "Class");
} else if ($type == 'faculty') {
    $filename = "faculties_list_" . date('Y-m-d') . ".csv";
    $query = $conn->query("SELECT school_id, firstname, lastname, email, password_text FROM faculty_list ORDER BY lastname ASC");
    $header = array("School ID", "First Name", "Last Name", "Email", "Password");
} else if ($type == 'admin') {
    $filename = "admins_list_" . date('Y-m-d') . ".csv";
    $query = $conn->query("SELECT firstname, lastname, email, password_text FROM users WHERE role = 'admin' ORDER BY lastname ASC");
    $header = array("First Name", "Last Name", "Email", "Password");
} else if ($type == 'superadmin') {
    $filename = "superadmins_list_" . date('Y-m-d') . ".csv";
    $query = $conn->query("SELECT firstname, lastname, email, password_text FROM users WHERE role = 'superadmin' ORDER BY lastname ASC");
    $header = array("First Name", "Last Name", "Email", "Password");
} else if ($type == 'questionnaire') {
    $filename = "questionnaire_" . date('Y-m-d') . ".csv";
    $where = $academic_id > 0 ? " WHERE academic_id = $academic_id" : "";
    $query = $conn->query("SELECT q.question, c.order_by FROM question_list q LEFT JOIN criteria_list c ON q.criteria_id = c.id $where ORDER BY c.order_by ASC, q.id ASC");
    $header = array("Question", "Criteria Order");
} else if ($type == 'criteria') {
    $filename = "criteria_list_" . date('Y-m-d') . ".csv";
    $query = $conn->query("SELECT criteria, order_by FROM criteria_list ORDER BY order_by ASC");
    $header = array("Criteria", "Order By");
} else if ($type == 'class') {
    $filename = "class_list_" . date('Y-m-d') . ".csv";
    $query = $conn->query("SELECT class_name FROM class_list ORDER BY class_name ASC");
    $header = array("Class Name");
} else if ($type == 'subject') {
    $filename = "subject_list_" . date('Y-m-d') . ".csv";
    $query = $conn->query("SELECT subject_code, subject_name FROM subject_list ORDER BY subject_name ASC");
    $header = array("Subject Code", "Subject Name");
} else if ($type == 'restriction') {
    $filename = "restriction_list_" . date('Y-m-d') . ".csv";
    $query = $conn->query("SELECT 
                            CONCAT(a.year, ' ', IF(a.semester = 1, '1st Sem', '2nd Sem')) as academic_year,
                            CONCAT(f.firstname, ' ', f.lastname) as faculty_name,
                            c.class_name,
                            CONCAT(s.subject_code, ' - ', s.subject_name) as subject
                          FROM restriction_list r 
                          JOIN academic_list a ON r.academic_id = a.id 
                          JOIN faculty_list f ON r.faculty_id = f.id 
                          JOIN class_list c ON r.class_id = c.id 
                          JOIN subject_list s ON r.subject_id = s.id 
                          ORDER BY a.year DESC, a.semester DESC, f.lastname ASC");
    $header = array("Academic Year", "Faculty Member", "Class", "Subject");
} else {
    echo "Invalid export type.";
    exit();
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');
fputcsv($output, $header);

while ($row = $query->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>
