<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
check_auth();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    set_flash('error', "Invalid student ID.");
    header("Location: students.php");
    exit;
}

// Fetch Existing Record
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    set_flash('error', "Student record not found.");
    header("Location: students.php");
    exit;
}

$errors = [];
$fullname = $student['fullname'];
$email = $student['email'];
$mobile = $student['mobile'];
$dob = $student['dob'];
$gender = $student['gender'];
$course = $student['course'];
$semester = $student['semester'];
$hobbies = !empty($student['hobbies']) ? array_map('trim', explode(',', $student['hobbies'])) : [];
$address = $student['address'];
$current_photo = $student['photo'] ?: 'default_avatar.svg';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $semester = trim($_POST['semester'] ?? '');
    $hobbies = $_POST['hobbies'] ?? [];
    $address = trim($_POST['address'] ?? '');
    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validations
    if (empty($fullname)) $errors[] = "Full Name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (empty($mobile) || !preg_match('/^[0-9]{10}$/', preg_replace('/[^0-9]/', '', $mobile))) $errors[] = "Mobile must be 10 digits.";
    if (empty($dob)) $errors[] = "Date of Birth is required.";
    if (empty($gender)) $errors[] = "Gender is required.";
    if (empty($course)) $errors[] = "Course is required.";
    if (empty($semester)) $errors[] = "Semester is required.";
    if (empty($address)) $errors[] = "Address is required.";

    // Check unique email except current student
    if (empty($errors)) {
        $stmt_check = $pdo->prepare("SELECT id FROM students WHERE email = ? AND id != ?");
        $stmt_check->execute([$email, $id]);
        if ($stmt_check->fetch()) {
            $errors[] = "The email '$email' is already in use by another student.";
        }
    }

    // Handle Password Change if entered
    if (!empty($new_password)) {
        if (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters.";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }
    }

    // Handle New Photo Upload
    $photo_filename = $current_photo;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['photo']['tmp_name'];
        $file_name = $_FILES['photo']['name'];
        $file_size = $_FILES['photo']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
        if (!in_array($file_ext, $allowed_exts)) {
            $errors[] = "Photo format not allowed.";
        } elseif ($file_size > 2 * 1024 * 1024) {
            $errors[] = "Photo size must be less than 2MB.";
        } else {
            $upload_dir = __DIR__ . '/uploads/';
            $new_name = 'student_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
            if (move_uploaded_file($file_tmp, $upload_dir . $new_name)) {
                $photo_filename = $new_name;
            }
        }
    }

    // Execute Update
    if (empty($errors)) {
        $hobbies_str = !empty($hobbies) ? implode(', ', $hobbies) : '';

        try {
            if (!empty($new_password)) {
                $hashed_pass = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $pdo->prepare("
                    UPDATE students 
                    SET fullname = ?, email = ?, mobile = ?, dob = ?, gender = ?, course = ?, semester = ?, hobbies = ?, address = ?, photo = ?, password = ?
                    WHERE id = ?
                ");
                $update_stmt->execute([$fullname, $email, $mobile, $dob, $gender, $course, $semester, $hobbies_str, $address, $photo_filename, $hashed_pass, $id]);
            } else {
                $update_stmt = $pdo->prepare("
                    UPDATE students 
                    SET fullname = ?, email = ?, mobile = ?, dob = ?, gender = ?, course = ?, semester = ?, hobbies = ?, address = ?, photo = ?
                    WHERE id = ?
                ");
                $update_stmt->execute([$fullname, $email, $mobile, $dob, $gender, $course, $semester, $hobbies_str, $address, $photo_filename, $id]);
            }

            set_flash('success', "Student record updated successfully!");
            header("Location: students.php");
            exit;
        } catch (Exception $e) {
            $errors[] = "Update failed: " . $e->getMessage();
        }
    }
}

$page_title = 'Edit Student - #' . $id;
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Student Information</h3>
            <p class="text-muted small mb-0">Phase 1 (e) - Update existing student record #<?= $id ?></p>
        </div>
        <a href="students.php" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Student List
        </a>
    </div>

    <div class="card card-custom shadow-sm">
        <div class="card-body p-4 p-md-5">
            <div id="formAlertBox"></div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <strong>Please correct the errors:</strong>
                    <ul class="mb-0 ps-3 mt-1">
                        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form id="studentRegistrationForm" data-mode="edit" action="student_edit.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data" novalidate>
                <!-- Personal Details -->
                <h5 class="fw-bold text-secondary mb-3 pb-2 border-bottom">Personal Details</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="fullname" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="fullname" name="fullname" value="<?= htmlspecialchars($fullname) ?>" required>
                        <div class="invalid-feedback">Full name is required.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                        <div class="invalid-feedback">Valid email is required.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="mobile" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="mobile" name="mobile" value="<?= htmlspecialchars($mobile) ?>" maxlength="10" required>
                        <div class="invalid-feedback">10-digit mobile number required.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="dob" name="dob" value="<?= htmlspecialchars($dob) ?>" required>
                        <div class="invalid-feedback">Date of birth is required.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label d-block">Gender <span class="text-danger">*</span></label>
                        <div id="genderContainer" class="d-flex gap-4 pt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" id="genderMale" value="Male" <?= $gender === 'Male' ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="genderMale">Male</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="Female" <?= $gender === 'Female' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="genderFemale">Female</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" id="genderOther" value="Other" <?= $gender === 'Other' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="genderOther">Other</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Academic Info -->
                <h5 class="fw-bold text-secondary mb-3 pb-2 border-bottom">Academic Details</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="course" class="form-label">Course <span class="text-danger">*</span></label>
                        <select class="form-select" id="course" name="course" required>
                            <?php foreach (['BCA', 'BBA', 'MCA', 'MBA', 'B.Tech (CSE)', 'B.Tech (IT)', 'B.Sc (CS)', 'M.Tech'] as $c): ?>
                                <option value="<?= $c ?>" <?= $course === $c ? 'selected' : '' ?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                        <select class="form-select" id="semester" name="semester" required>
                            <?php for ($i = 1; $i <= 8; $i++): $s_val = "Semester $i"; ?>
                                <option value="<?= $s_val ?>" <?= $semester === $s_val ? 'selected' : '' ?>><?= $s_val ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label d-block">Hobbies</label>
                        <div class="d-flex flex-wrap gap-4 pt-1">
                            <?php foreach (['Reading', 'Sports', 'Music', 'Coding', 'Other'] as $hob): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="hobbies[]" id="hob_<?= strtolower($hob) ?>" value="<?= $hob ?>" <?= in_array($hob, (array)$hobbies) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="hob_<?= strtolower($hob) ?>"><?= $hob ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Address & Photo -->
                <h5 class="fw-bold text-secondary mb-3 pb-2 border-bottom">Address & Profile Photo</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label for="address" class="form-label">Residential Address <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="address" name="address" rows="4" required><?= htmlspecialchars($address) ?></textarea>
                    </div>
                    <div class="col-md-4 text-center">
                        <label class="form-label d-block text-start">Current / New Photo</label>
                        <div class="mb-2">
                            <img id="photoPreview" src="uploads/<?= htmlspecialchars($current_photo) ?>" alt="Photo" class="rounded-circle border shadow-sm" style="width: 90px; height: 90px; object-fit: cover;">
                        </div>
                        <input class="form-control form-control-sm" type="file" id="photo" name="photo" accept="image/*">
                        <div class="form-text small">Leave empty to keep existing photo</div>
                    </div>
                </div>

                <!-- Update Password (Optional) -->
                <h5 class="fw-bold text-secondary mb-3 pb-2 border-bottom">Update Password <span class="fw-normal text-muted fs-6">(Optional)</span></h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep unchanged">
                    </div>
                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter new password">
                    </div>
                </div>

                <div class="d-flex gap-3 justify-content-end pt-3 border-top">
                    <a href="students.php" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-success px-5 fw-semibold shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
