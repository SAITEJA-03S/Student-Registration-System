<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
check_auth();

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // Fetch photo to clean up file system
    $stmt = $pdo->prepare("SELECT photo, fullname FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        // Delete photo if not default
        if (!empty($student['photo']) && !in_array($student['photo'], ['default_avatar.svg', 'default_avatar.png'])) {
            $photo_path = __DIR__ . '/uploads/' . $student['photo'];
            if (file_exists($photo_path)) {
                @unlink($photo_path);
            }
        }

        // Delete from database
        $del_stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $del_stmt->execute([$id]);

        set_flash('success', "Student '" . htmlspecialchars($student['fullname']) . "' was successfully deleted.");
    } else {
        set_flash('error', "Student not found.");
    }
} else {
    set_flash('error', "Invalid student ID.");
}

header("Location: students.php");
exit;
