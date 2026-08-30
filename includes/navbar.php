<?php
require_once __DIR__ . '/auth.php';
$is_logged = is_logged_in();
$user = current_user();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top py-3 no-print">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $is_logged ? 'dashboard.php' : 'login.php' ?>">
            <div class="brand-icon shadow-sm">
                <i class="bi bi-mortarboard-fill fs-5"></i>
            </div>
            <div>
                <span class="fw-bold tracking-tight text-white">Student Registration</span>
                <span class="badge bg-primary bg-opacity-75 ms-1 fs-xs">v1.0</span>
            </div>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <?php if ($is_logged): ?>
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'dashboard.php' ? 'active fw-bold text-white' : '' ?>" href="dashboard.php">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= in_array($current_page, ['students.php', 'student_view.php', 'student_edit.php']) ? 'active fw-bold text-white' : '' ?>" href="students.php">
                        <i class="bi bi-people me-1"></i> Student List
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'student_add.php' ? 'active fw-bold text-white' : '' ?>" href="student_add.php">
                        <i class="bi bi-person-plus me-1"></i> Add Student
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'reports.php' ? 'active fw-bold text-white' : '' ?>" href="reports.php">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i> Reports
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <div class="text-light d-none d-md-block text-end">
                    <div class="small fw-semibold text-white"><?= htmlspecialchars($user['username']) ?></div>
                    <div class="text-white-50" style="font-size: 0.75rem;"><?= ucfirst($user['role']) ?></div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-light btn-sm rounded-circle p-2 dropdown-toggle dropdown-toggle-split" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle fs-6"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                        <li><h6 class="dropdown-header">Signed in as <strong><?= htmlspecialchars($user['username']) ?></strong></h6></li>
                        <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="student_add.php"><i class="bi bi-person-plus me-2"></i>New Registration</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
                <a href="logout.php" class="btn btn-danger btn-sm px-3 rounded-pill d-none d-lg-inline-flex align-items-center gap-1">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
            <?php else: ?>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 gap-2">
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'login.php' ? 'active text-white' : '' ?>" href="login.php">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login
                    </a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-primary btn-sm px-3 rounded-pill" href="register.php">
                        <i class="bi bi-pencil-square me-1"></i> Register Student
                    </a>
                </li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>

