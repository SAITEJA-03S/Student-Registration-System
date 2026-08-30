<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header("Location: dashboard.php");
    exit;
}

$error_message = '';
$username_val = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $username_val = $username;

    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password!";
    } else {
        // Check in users table
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            set_flash('success', "Welcome back, " . htmlspecialchars($user['username']) . "!");
            header("Location: dashboard.php");
            exit;
        } else {
            // Also allow student login via email
            $stmt_st = $pdo->prepare("SELECT * FROM students WHERE email = ?");
            $stmt_st->execute([$username]);
            $st = $stmt_st->fetch(PDO::FETCH_ASSOC);

            if ($st && password_verify($password, $st['password'])) {
                $_SESSION['user_id'] = $st['id'];
                $_SESSION['username'] = $st['fullname'];
                $_SESSION['role'] = 'student';
                set_flash('success', "Welcome back, " . htmlspecialchars($st['fullname']) . "!");
                header("Location: dashboard.php");
                exit;
            } else {
                // Appropriate error message as per Phase 1 (b)
                $error_message = "Invalid Username or Password!";
            }
        }
    }
}

$flash_success = get_flash('success');
$flash_error = get_flash('error') ?? $error_message;
$page_title = 'Login - Student Registration System';
require_once __DIR__ . '/includes/header.php';
?>

<div class="login-wrapper">
    <div class="card card-custom login-card shadow-lg">
        <!-- Header / Live Time Ribbon -->
        <div class="p-4 text-center border-bottom bg-white">
            <div class="brand-icon mb-3 shadow">
                <i class="bi bi-mortarboard-fill fs-2"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1">Student Registration System</h4>
            <p class="text-muted small mb-3">Web Development using PHP (504)</p>

            <!-- Requirement (a): Show Current Date & Time on Login Page -->
            <div id="liveClockDisplay" class="live-time-badge shadow-sm w-100 justify-content-center">
                <span><i class="bi bi-calendar-event me-1"></i>Date : <strong id="liveDateDisplay"><?= date('d M Y') ?></strong></span>
                <span class="mx-2 text-muted">|</span>
                <span><i class="bi bi-clock me-1"></i>Time : <strong id="liveTimeDisplay"><?= date('h:i:s A') ?></strong></span>
            </div>
        </div>

        <div class="card-body p-4 p-md-5 bg-white">
            <h5 class="fw-bold mb-4 text-center text-primary">
                <i class="bi bi-lock-fill me-1"></i> System Login
            </h5>

            <!-- Requirement (b): Login Page with Appropriate Error Message -->
            <?php if (!empty($flash_error)): ?>
                <div class="alert alert-danger d-flex align-items-center shadow-sm py-2 px-3 mb-4 rounded-3 border-danger-subtle" role="alert">
                    <i class="bi bi-exclamation-octagon-fill fs-5 me-2 flex-shrink-0"></i>
                    <div>
                        <strong><?= htmlspecialchars($flash_error) ?></strong>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($flash_success)): ?>
                <div class="alert alert-success d-flex align-items-center shadow-sm py-2 px-3 mb-4 rounded-3" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 me-2 flex-shrink-0"></i>
                    <div><?= htmlspecialchars($flash_success) ?></div>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" autocomplete="off">
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="bi bi-person me-1"></i> Username or Email
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-person-fill text-muted"></i></span>
                        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($username_val) ?>" placeholder="Enter admin or student email" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <label for="password" class="form-label">
                            <i class="bi bi-key me-1"></i> Password
                        </label>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-lock-fill text-muted"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg fw-semibold shadow-sm py-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login
                    </button>
                </div>

                <div class="text-center pt-2">
                    <p class="text-muted small mb-2">New student not registered yet?</p>
                    <a href="register.php" class="btn btn-outline-secondary btn-sm rounded-pill px-4">
                        <i class="bi bi-person-plus me-1"></i> Register Student Account
                    </a>
                </div>
            </form>

            <!-- Quick Demo Credentials Box -->
            <div class="mt-4 p-3 bg-light rounded-3 border text-start small">
                <div class="fw-bold text-dark mb-1"><i class="bi bi-info-circle-fill text-primary me-1"></i> Default Demo Credentials:</div>
                <div class="text-muted">Admin: <code class="user-select-all fw-bold">admin</code> / Password: <code class="user-select-all fw-bold">admin123</code></div>
                <div class="text-muted">Student: <code class="user-select-all fw-bold">rahul@example.com</code> / Password: <code class="user-select-all fw-bold">student123</code></div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/clock.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
