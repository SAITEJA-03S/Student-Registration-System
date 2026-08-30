<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
check_auth();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: students.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    set_flash('error', "Student record not found.");
    header("Location: students.php");
    exit;
}

$photo_src = 'uploads/' . ($student['photo'] ?: 'default_avatar.svg');
if (!file_exists($photo_src)) {
    $photo_src = 'uploads/default_avatar.svg';
}

$page_title = 'Student Profile - ' . htmlspecialchars($student['fullname']);
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-person-badge text-primary me-2"></i>Student Details</h3>
            <p class="text-muted small mb-0">Record ID: #<?= $student['id'] ?> | Registered: <?= date('d M Y, h:i A', strtotime($student['created_at'])) ?></p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-printer me-1"></i> Print Profile
            </button>
            <a href="student_edit.php?id=<?= $student['id'] ?>" class="btn btn-primary rounded-pill px-3">
                <i class="bi bi-pencil-square me-1"></i> Edit Profile
            </a>
            <a href="students.php" class="btn btn-outline-dark rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Printable Header (Only visible on print) -->
    <div class="print-header">
        <h2>STUDENT REGISTRATION SYSTEM</h2>
        <h4>Official Student Record Summary</h4>
        <p>Date Generated: <?= date('d M Y, h:i A') ?></p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-custom student-id-card shadow-lg mb-4">
                <div class="id-card-header">
                    <h4 class="fw-bold mb-1 text-white">STUDENT ID CARD</h4>
                    <p class="small text-white-50 mb-0">Student Registration System - PHP (504)</p>
                </div>

                <div class="card-body p-4 text-center">
                    <img src="<?= htmlspecialchars($photo_src) ?>" alt="Photo" class="id-avatar mb-3">
                    <h3 class="fw-bold text-dark mb-1"><?= htmlspecialchars($student['fullname']) ?></h3>
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <span class="badge bg-primary px-3 py-2 fs-6"><?= htmlspecialchars($student['course']) ?></span>
                        <span class="badge bg-secondary px-3 py-2 fs-6"><?= htmlspecialchars($student['semester']) ?></span>
                    </div>

                    <hr class="my-4">

                    <div class="row g-3 text-start px-md-4">
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase">Student ID</label>
                            <p class="fw-semibold fs-5 text-primary mb-0">#<?= str_pad($student['id'], 5, '0', STR_PAD_LEFT) ?></p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase">Gender</label>
                            <p class="fw-semibold mb-0"><?= htmlspecialchars($student['gender']) ?></p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase">Date of Birth</label>
                            <p class="fw-semibold mb-0"><?= date('d F Y', strtotime($student['dob'])) ?></p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase">Mobile Number</label>
                            <p class="fw-semibold mb-0 font-monospace"><?= htmlspecialchars($student['mobile']) ?></p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase">Email Address</label>
                            <p class="fw-semibold mb-0"><?= htmlspecialchars($student['email']) ?></p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase">Hobbies</label>
                            <p class="fw-semibold mb-0"><?= !empty($student['hobbies']) ? htmlspecialchars($student['hobbies']) : 'None specified' ?></p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-bold text-uppercase">Residential Address</label>
                            <p class="fw-semibold mb-0 text-secondary"><?= nl2br(htmlspecialchars($student['address'])) ?></p>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light p-3 text-center text-muted small">
                    <i class="bi bi-shield-check text-success me-1"></i> Verified Student Record in Database
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
