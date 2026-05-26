<?php
session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['login_type'] != 'admin') {
    exit();
}
require_once '../evaluation_db/db_connect.php';

// Filters
$academic_id = isset($_GET['academic_id']) ? (int)$_GET['academic_id'] : 0;
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$where = " WHERE 1=1 ";
if ($academic_id > 0) $where .= " AND el.academic_id = $academic_id ";
if ($class_id > 0) $where .= " AND el.class_id = $class_id ";
if (!empty($search)) {
    $where .= " AND (s.school_id LIKE '%$search%' OR s.firstname LIKE '%$search%' OR s.lastname LIKE '%$search%' OR s.email LIKE '%$search%') ";
}

$query_str = "SELECT el.*, s.school_id, s.firstname as sf, s.lastname as sl, s.email, c.class_name, f.firstname as ff, f.lastname as fl, a.year, a.semester 
              FROM evaluation_list el 
              JOIN student_list s ON el.student_id = s.id 
              JOIN class_list c ON el.class_id = c.id 
              JOIN faculty_list f ON el.faculty_id = f.id 
              JOIN academic_list a ON el.academic_id = a.id 
              $where 
              ORDER BY el.date_created DESC";

$results = $conn->query($query_str);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=completed_evaluations_'.date('YmdHis').'.csv');

$output = fopen('php://output', 'w');
fputcsv($output, array('Student ID', 'Student Name', 'Email', 'Class', 'Professor', 'Academic Year', 'Semester', 'Date Evaluated'));

if ($results) {
    while ($row = $results->fetch_assoc()) {
        fputcsv($output, array(
            $row['school_id'],
            $row['sf'].' '.$row['sl'],
            $row['email'],
            $row['class_name'],
            $row['ff'].' '.$row['fl'],
            $row['year'],
            ($row['semester'] == 1 ? '1st' : '2nd') . ' Sem',
            date("Y-m-d H:i:s", strtotime($row['date_created']))
        ));
    }
}
fclose($output);
exit();
?>
