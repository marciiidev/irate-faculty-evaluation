<?php
session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['login_type'] != 'admin') {
    exit("Access Denied");
}
require_once '../evaluation_db/db_connect.php';

// Sentiment Analysis Helper
function analyzeSentiment($comment) {
    if (empty($comment)) return "Positive";
    $positives = [
        'good', 'excellent', 'great', 'awesome', 'nice', 'best', 'helpful', 'wonderful', 'effective', 'amazing', 'well', 'clear', 'organized', 'passionate', 'kind', 'approachable', 'fair', 'superb', 'fast', 'brilliant', 'patient', 'understanding', 'efficient', 'inspiring',
        'magaling', 'mahusay', 'mabait', 'matulungin', 'maayos', 'madaling lapitan', 'masaya', 'energetic', 'sipag', 'masipag', 'maganda', 'paborito', 'lodi', 'sulit', 'dabest', 'petmalu', 'hanga', 'bilis', 'linaw', 'unawa', 'nagtuturo', 'malinaw'
    ];
    $negatives = [
        'bad', 'poor', 'terrible', 'worst', 'unhelpful', 'boring', 'confused', 'lazy', 'difficult', 'late', 'disorganized', 'strict', 'rude', 'unfair', 'unprofessional', 'mean', 'slow', 'loud', 'angry', 'boring', 'bias',
        'masungit', 'tamad', 'mabagal', 'mahirap intindihin', 'nakakaantok', 'galit', 'mura', 'pangit', 'laging wala', 'wala kaming matutunan', 'magulo', 'bias', 'unfair', 'kupad', 'tulog', 'late', 'absent', 'hindi nagtuturo', 'di nagtuturo'
    ];
    
    $comment = mb_strtolower($comment);
    $posCount = 0; $negCount = 0;
    
    foreach ($negatives as $word) { if (mb_strpos($comment, $word) !== false) $negCount += 2; }
    $negators = ['not', 'hindi', 'di', 'no', 'never', 'wala', 'kulang'];
    foreach ($positives as $word) {
        if (mb_strpos($comment, $word) !== false) {
            $isNegated = false;
            foreach ($negators as $neg) {
                if (mb_strpos($comment, $neg . ' ' . $word) !== false || mb_strpos($comment, $neg . $word) !== false) { $isNegated = true; break; }
            }
            if ($isNegated) $negCount += 2;
            else $posCount++;
        }
    }
    return ($posCount >= $negCount && $posCount > 0) ? "Positive" : "Negative";
}

$f_year = isset($_GET['year']) ? $_GET['year'] : '';
$f_semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;
$f_faculty = isset($_GET['faculty_id']) ? (int)$_GET['faculty_id'] : 0;

// ONLY SHOW CLOSED/ARCHIVED SEMESTERS (status = 2)
$where = " WHERE a.status = 2 ";
if (!empty($f_year)) $where .= " AND a.year = '" . $conn->real_escape_string($f_year) . "' ";
if ($f_semester > 0) $where .= " AND a.semester = $f_semester ";
if ($f_faculty > 0) $where .= " AND el.faculty_id = $f_faculty ";

$query = "SELECT 
            el.faculty_id, 
            el.academic_id, 
            f.firstname, 
            f.lastname, 
            f.school_id,
            a.year, 
            a.semester,
            COUNT(DISTINCT el.id) as participants
          FROM evaluation_list el
          JOIN faculty_list f ON el.faculty_id = f.id
          JOIN academic_list a ON el.academic_id = a.id
          $where
          GROUP BY el.faculty_id, el.academic_id
          ORDER BY a.year DESC, a.semester DESC, f.lastname ASC";

$res = $conn->query($query);
$records = [];
while($row = $res->fetch_assoc()) {
    $fid = $row['faculty_id'];
    $aid = $row['academic_id'];
    
    // Rating
    $rating_res = $conn->query("SELECT AVG(ea.rating) as avg_rating FROM evaluation_answers ea JOIN evaluation_list el ON ea.evaluation_id = el.id WHERE el.faculty_id = $fid AND el.academic_id = $aid")->fetch_assoc();
    $row['rating'] = number_format($rating_res['avg_rating'] ?? 0, 2);
    
    // Descriptive
    $desc = "N/A";
    $rating_val = (float)$row['rating'];
    if ($rating_val >= 4.50) $desc = "Outstanding";
    else if ($rating_val >= 3.50) $desc = "Very Satisfactory";
    else if ($rating_val >= 2.50) $desc = "Satisfactory";
    else if ($rating_val >= 1.50) $desc = "Fair";
    else if ($rating_val >= 1.00) $desc = "Poor";
    $row['desc'] = $desc;

    // Remarks
    $comments_res = $conn->query("SELECT comment FROM evaluation_comments ec JOIN evaluation_list el ON ec.evaluation_id = el.id WHERE el.faculty_id = $fid AND el.academic_id = $aid");
    $pos = 0; $neg = 0;
    while($c = $comments_res->fetch_assoc()) {
        $sentiment = analyzeSentiment($c['comment']);
        if ($sentiment == "Positive") $pos++;
        else $neg++;
    }
    $row['pos'] = $pos;
    $row['neg'] = $neg;
    
    $records[] = $row;
}

// RANKING LOGIC
usort($records, function($a, $b) {
    if ((float)$a['rating'] == (float)$b['rating']) return 0;
    return ((float)$a['rating'] > (float)$b['rating']) ? -1 : 1;
});

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Master_Records_Ranking_'.date('YmdHis').'.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Rank', 'Faculty ID', 'Firstname', 'Lastname', 'Academic Year', 'Semester', 'Participants', 'Overall Rating', 'Descriptive Rating', 'Positive Remarks', 'Constructive Remarks']);

$rank = 1;
foreach($records as $row) {
    fputcsv($output, [
        $rank,
        $row['school_id'],
        $row['firstname'],
        $row['lastname'],
        $row['year'],
        $row['semester'] == 1 ? '1st' : '2nd',
        $row['participants'],
        $row['rating'],
        $row['desc'],
        $row['pos'],
        $row['neg']
    ]);
    $rank++;
}
fclose($output);
exit();
