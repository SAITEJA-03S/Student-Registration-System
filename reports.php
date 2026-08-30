<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
check_auth();

// Report Filter Parameters
$course_filter = trim($_GET['course'] ?? '');
$sem_filter = trim($_GET['semester'] ?? '');
$gender_filter = trim($_GET['gender'] ?? '');

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

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Fetch filtered students
$stmt = $pdo->prepare("SELECT * FROM students $where_sql ORDER BY course ASC, semester ASC, fullname ASC");
$stmt->execute($params);
$report_students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_matched = count($report_students);

// Aggregations
$course_counts = [];
$gender_counts = ['Male' => 0, 'Female' => 0, 'Other' => 0];

foreach ($report_students as $st) {
    $c = $st['course'];
    $g = $st['gender'];
    $course_counts[$c] = ($course_counts[$c] ?? 0) + 1;
    if (isset($gender_counts[$g])) {
        $gender_counts[$g]++;
    }
}

$page_title = 'Student Reports & Analytics';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    <!-- Header Actions (Hidden on Print) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 no-print">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-file-earmark-bar-graph text-primary me-2"></i>Student Reports</h3>
            <p class="text-muted small mb-0">Phase 1 (g) - Structured Reports for Viewing, Exporting &amp; Printing</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm">
                <i class="bi bi-printer me-1"></i> Print / Save PDF
            </button>
            <a href="export.php?format=csv<?= !empty($course_filter) ? '&course=' . urlencode($course_filter) : '' ?><?= !empty($sem_filter) ? '&semester=' . urlencode($sem_filter) : '' ?><?= !empty($gender_filter) ? '&gender=' . urlencode($gender_filter) : '' ?>" class="btn btn-success rounded-pill px-3 shadow-sm">
                <i class="bi bi-download me-1"></i> Export to CSV
            </a>
        </div>
    </div>

    <!-- Print Header (Only visible on paper / PDF) -->
    <div class="print-header">
        <h2 class="fw-bold">STUDENT REGISTRATION SYSTEM</h2>
        <h5>Comprehensive Student Enrollment Report</h5>
        <p class="mb-0">Generated On: <strong><?= date('d F Y, h:i A') ?></strong> | Filter: Course: <strong><?= $course_filter ?: 'All' ?></strong>, Semester: <strong><?= $sem_filter ?: 'All' ?></strong>, Gender: <strong><?= $gender_filter ?: 'All' ?></strong></p>
    </div>

    <!-- Filter Card (Hidden on Print) -->
    <div class="card card-custom mb-4 shadow-sm border-0 no-print">
        <div class="card-body p-3">
            <form action="reports.php" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="form-label small mb-1 fw-bold">Course Filter</label>
                    <select name="course" class="form-select form-select-sm">
                        <option value="">-- All Courses --</option>
                        <?php foreach (['BCA', 'BBA', 'MCA', 'MBA', 'B.Tech (CSE)', 'B.Tech (IT)', 'B.Sc (CS)', 'M.Tech'] as $c): ?>
                            <option value="<?= $c ?>" <?= $course_filter === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1 fw-bold">Semester Filter</label>
                    <select name="semester" class="form-select form-select-sm">
                        <option value="">-- All Semesters --</option>
                        <?php for ($i = 1; $i <= 8; $i++): $s_val = "Semester $i"; ?>
                            <option value="<?= $s_val ?>" <?= $sem_filter === $s_val ? 'selected' : '' ?>><?= $s_val ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1 fw-bold">Gender Filter</label>
                    <select name="gender" class="form-select form-select-sm">
                        <option value="">-- All Genders --</option>
                        <option value="Male" <?= $gender_filter === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $gender_filter === 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?= $gender_filter === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-3 pt-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel me-1"></i> Filter Report</button>
                    <?php if (!empty($course_filter) || !empty($sem_filter) || !empty($gender_filter)): ?>
                        <a href="reports.php" class="btn btn-outline-secondary btn-sm" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-custom p-3 border-0 bg-primary text-white shadow-sm">
                <div class="small fw-semibold text-white-50">Total Matched Students</div>
                <div class="fs-3 fw-bold"><?= $total_matched ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom p-3 border-0 bg-success text-white shadow-sm">
                <div class="small fw-semibold text-white-50">Gender Breakdown</div>
                <div class="fs-6 fw-bold mt-1">
                    Male: <?= $gender_counts['Male'] ?> | Female: <?= $gender_counts['Female'] ?> | Other: <?= $gender_counts['Other'] ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom p-3 border-0 bg-dark text-white shadow-sm">
                <div class="small fw-semibold text-white-50">Courses Represented</div>
                <div class="fs-3 fw-bold"><?= count($course_counts) ?></div>
            </div>
        </div>
    </div>

    <!-- Report Data Table -->
    <div class="card card-custom shadow-sm border-0 mb-4">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-secondary">
                <i class="bi bi-table text-primary me-2"></i>Report Records Listing
            </h6>
            <span class="badge bg-secondary-subtle text-secondary"><?= $total_matched ?> Records Found</span>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Gender</th>
                        <th>Course</th>
                        <th>Semester</th>
                        <th>DOB</th>
                        <th>Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($report_students)): ?>
                        <?php foreach ($report_students as $idx => $st): ?>
                        <tr>
                            <td class="text-center font-monospace"><?= $idx + 1 ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($st['fullname']) ?></td>
                            <td><?= htmlspecialchars($st['email']) ?></td>
                            <td class="font-monospace"><?= htmlspecialchars($st['mobile']) ?></td>
                            <td><?= htmlspecialchars($st['gender']) ?></td>
                            <td><span class="badge bg-primary text-white"><?= htmlspecialchars($st['course']) ?></span></td>
                            <td><?= htmlspecialchars($st['semester']) ?></td>
                            <td><?= date('d-m-Y', strtotime($st['dob'])) ?></td>
                            <td class="small"><?= htmlspecialchars($st['address']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No student records match the selected report filter.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
