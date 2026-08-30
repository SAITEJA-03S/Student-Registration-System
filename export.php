<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
check_auth();

$course_filter = trim($_GET['course'] ?? '');
$sem_filter = trim($_GET['semester'] ?? '');
$gender_filter = trim($_GET['gender'] ?? '');
$search = trim($_GET['search'] ?? '');

$where_clauses = [];
$params = [];

if (!empty($course_filter)) {
    $where_clauses[] = "course = ?";
    $params[] = $course_filter;
}
if (!empty($sem_filter)) {
    $where_clauses[] = "semester = ?";
    $params[] = $sem_filter;
}
if (!empty($gender_filter)) {
    $where_clauses[] = "gender = ?";
    $params[] = $gender_filter;
}
if (!empty($search)) {
    $where_clauses[] = "(fullname LIKE ? OR email LIKE ? OR mobile LIKE ? OR course LIKE ? OR address LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term, $search_term]);
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

$stmt = $pdo->prepare("SELECT id, fullname, email, mobile, dob, gender, course, semester, hobbies, address, created_at FROM students $where_sql ORDER BY id ASC");
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = "student_report_" . date('Y-m-d_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel compatibility
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

// Header row
fputcsv($output, ['ID', 'Full Name', 'Email Address', 'Mobile Number', 'Date of Birth', 'Gender', 'Course', 'Semester', 'Hobbies', 'Address', 'Registration Date']);

// Data rows
foreach ($rows as $row) {
    fputcsv($output, [
        $row['id'],
        $row['fullname'],
        $row['email'],
        $row['mobile'],
        $row['dob'],
        $row['gender'],
        $row['course'],
        $row['semester'],
        $row['hobbies'],
        $row['address'],
        $row['created_at']
    ]);
}

fclose($output);
exit;
