<?php
session_start();
require_once '../db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$subject_code = isset($_GET['code']) ? $_GET['code'] : '';

if (empty($subject_code)) {
    header('HTTP/1.1 400 Bad Request');
    exit('Subject code is required');
}

$query = "SELECT 
    sa.id, 
    sa.year, 
    sa.semester, 
    sa.section, 
    sa.faculty_id, 
    sa.is_active,
    f.name as faculty_name,
    ay.year_range as academic_year,
    (SELECT COUNT(*) FROM feedback fb WHERE fb.assignment_id = sa.id) as feedback_count
FROM subjects s
JOIN subject_assignments sa ON s.id = sa.subject_id
LEFT JOIN faculty f ON sa.faculty_id = f.id
LEFT JOIN academic_years ay ON sa.academic_year_id = ay.id
WHERE s.code = ?
ORDER BY sa.academic_year_id DESC, sa.year, sa.semester, sa.section";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $subject_code);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$assignments = [];
while ($row = mysqli_fetch_assoc($result)) {
    $assignments[] = $row;
}

header('Content-Type: application/json');
echo json_encode($assignments);
?>