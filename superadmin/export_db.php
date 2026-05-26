<?php
session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['login_type'] != 'superadmin') {
    header("location: ../index.php");
    exit();
}
require_once '../evaluation_db/db_connect.php';

$tables = array();
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
}

$sql = "CREATE DATABASE IF NOT EXISTS evaluation_db;\nUSE evaluation_db;\n\n";

foreach ($tables as $table) {
    $result = $conn->query("SELECT * FROM $table");
    $num_fields = $result->field_count;

    $sql .= "DROP TABLE IF EXISTS $table;";
    $row2 = $conn->query("SHOW CREATE TABLE $table")->fetch_row();
    $sql .= "\n\n" . $row2[1] . ";\n\n";

    for ($i = 0; $i < $num_fields; $i++) {
        while ($row = $result->fetch_row()) {
            $sql .= "INSERT INTO $table VALUES(";
            for ($j = 0; $j < $num_fields; $j++) {
                $row[$j] = addslashes($row[$j]);
                $row[$j] = str_replace("\n", "\\n", $row[$j]);
                if (isset($row[$j])) {
                    $sql .= '"' . $row[$j] . '"';
                } else {
                    $sql .= 'NULL';
                }
                if ($j < ($num_fields - 1)) {
                    $sql .= ',';
                }
            }
            $sql .= ");\n";
        }
    }
    $sql .= "\n\n\n";
}

$filename = 'evaluation_db_backup_' . date('Y-m-d_H-i-s') . '.sql';

header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $filename . "\"");
echo $sql;
exit();
?>
