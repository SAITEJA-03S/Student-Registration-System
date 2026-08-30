<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
check_auth();

// Fetch Summary Statistics
$total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$total_courses = $pdo->query("SELECT COUNT(DISTINCT course) FROM students")->fetchColumn();
$male_students = $pdo->query("SELECT COUNT(*) FROM students WHERE gender = 'Male'")->fetchColumn();
$female_students = $pdo->query("SELECT COUNT(*) FROM students WHERE gender = 'Female'")->fetchColumn();

// Fetch Course Distribution for Charts
$course_stats = $pdo->query("SELECT course, COUNT(*) as count FROM students GROUP BY course")->fetchAll(PDO::FETCH_ASSOC);
$course_labels = array_column($course_stats, 'course');
$course_counts = array_column($course_stats, 'count');

// Fetch Semester Distribution for Charts
$sem_stats = $pdo->query("SELECT semester, COUNT(*) as count FROM students GROUP BY semester ORDER BY semester ASC")->fetchAll(PDO::FETCH_ASSOC);
$sem_labels = array_column($sem_stats, 'semester');
$sem_counts = array_column($sem_stats, 'count');

// Fetch Recent 5 Students
$recent_students = $pdo->query("SELECT * FROM students ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

$flash_success = get_flash('success');
$page_title = 'Dashboard Overview';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    <!-- Welcome Banner -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-dark">
                Welcome, <?= htmlspecialchars(current_user()['username']) ?>! 👋
            </h2>
            <p class="text-muted small mb-0">Student Registration System - Administration &amp; Analytics Dashboard</p>
        </div>
        <div class="d-flex gap-2">
            <a href="reports.php" class="btn btn-outline-primary rounded-pill px-3 shadow-sm">
                <i class="bi bi-bar-chart-line me-1"></i> Analytics &amp; Reports
            </a>
            <a href="student_add.php" class="btn btn-primary rounded-pill px-3 shadow-sm">
                <i class="bi bi-person-plus me-1"></i> New Student
            </a>
        </div>
    </div>

    <?php if ($flash_success): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($flash_success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- 4 Stats Cards Grid -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card blue">
                <div class="small fw-semibold text-uppercase tracking-wider text-white-50">Total Students</div>
                <div class="fs-2 fw-bold my-1"><?= number_format($total_students) ?></div>
                <div class="small text-white-50"><i class="bi bi-arrow-up-circle me-1"></i>Enrolled in database</div>
                <i class="bi bi-people stat-icon"></i>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card green">
                <div class="small fw-semibold text-uppercase tracking-wider text-white-50">Active Courses</div>
                <div class="fs-2 fw-bold my-1"><?= number_format($total_courses) ?></div>
                <div class="small text-white-50"><i class="bi bi-mortarboard me-1"></i>Offered programs</div>
                <i class="bi bi-mortarboard stat-icon"></i>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card purple">
                <div class="small fw-semibold text-uppercase tracking-wider text-white-50">Male Students</div>
                <div class="fs-2 fw-bold my-1"><?= number_format($male_students) ?></div>
                <div class="small text-white-50"><i class="bi bi-gender-male me-1"></i>Registered candidates</div>
                <i class="bi bi-gender-male stat-icon"></i>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card amber">
                <div class="small fw-semibold text-uppercase tracking-wider text-white-50">Female Students</div>
                <div class="fs-2 fw-bold my-1"><?= number_format($female_students) ?></div>
                <div class="small text-white-50"><i class="bi bi-gender-female me-1"></i>Registered candidates</div>
                <i class="bi bi-gender-female stat-icon"></i>
            </div>
        </div>
    </div>

    <!-- Charts Row (Wireframe Dashboard Representation) -->
    <div class="row g-4 mb-4">
        <!-- Chart 1: Course Breakdown -->
        <div class="col-lg-6">
            <div class="card card-custom h-100 shadow-sm border-0">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-secondary">
                        <i class="bi bi-pie-chart-fill text-primary me-2"></i>Students by Course
                    </h6>
                    <a href="reports.php" class="small text-decoration-none">View full &rarr;</a>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-4">
                    <div style="width: 100%; max-height: 280px;">
                        <canvas id="courseChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart 2: Semester Distribution -->
        <div class="col-lg-6">
            <div class="card card-custom h-100 shadow-sm border-0">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-secondary">
                        <i class="bi bi-bar-chart-fill text-success me-2"></i>Students by Semester
                    </h6>
                    <a href="students.php" class="small text-decoration-none">View table &rarr;</a>
                </div>
                <div class="card-body p-4">
                    <div style="width: 100%; height: 260px;">
                        <canvas id="semesterChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Registrations Table -->
    <div class="card card-custom shadow-sm border-0 mb-4">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-secondary">
                <i class="bi bi-clock-history text-info me-2"></i>Recent Student Registrations
            </h6>
            <a href="students.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                View All Records (<?= $total_students ?>)
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Course</th>
                        <th>Semester</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_students)): ?>
                        <?php foreach ($recent_students as $st): 
                            $photo_src = 'uploads/' . ($st['photo'] ? $st['photo'] : 'default_avatar.svg');
                            if (!file_exists($photo_src)) {
                                $photo_src = 'uploads/default_avatar.svg';
                            }
                        ?>
                        <tr>
                            <td class="fw-bold text-muted">#<?= $st['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= htmlspecialchars($photo_src) ?>" alt="Photo" class="student-avatar-thumb" style="width: 36px; height: 36px;">
                                    <div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($st['fullname']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($st['gender']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($st['email']) ?></td>
                            <td class="font-monospace small"><?= htmlspecialchars($st['mobile']) ?></td>
                            <td><span class="badge bg-primary-subtle text-primary fw-semibold"><?= htmlspecialchars($st['course']) ?></span></td>
                            <td><?= htmlspecialchars($st['semester']) ?></td>
                            <td class="text-center">
                                <a href="student_view.php?id=<?= $st['id'] ?>" class="btn btn-outline-info btn-action" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="student_edit.php?id=<?= $st['id'] ?>" class="btn btn-outline-primary btn-action" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No registrations found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Course Doughnut Chart
    const ctxCourse = document.getElementById('courseChart');
    if (ctxCourse) {
        new Chart(ctxCourse, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($course_labels) ?>,
                datasets: [{
                    data: <?= json_encode($course_counts) ?>,
                    backgroundColor: [
                        '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6',
                        '#ec4899', '#06b6d4', '#84cc16', '#64748b'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });
    }

    // Semester Bar Chart
    const ctxSem = document.getElementById('semesterChart');
    if (ctxSem) {
        new Chart(ctxSem, {
            type: 'bar',
            data: {
                labels: <?= json_encode($sem_labels) ?>,
                datasets: [{
                    label: 'Students Enrolled',
                    data: <?= json_encode($sem_counts) ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.85)',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
