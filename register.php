<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$errors = [];
$success_msg = '';

$fullname = '';
$email = '';
$mobile = '';
$dob = '';
$gender = '';
$course = '';
$semester = '';
$hobbies = [];
$address = '';

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
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Server-Side Validations
    if (empty($fullname)) $errors[] = "Full Name is required.";
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($mobile)) {
        $errors[] = "Mobile number is required.";
    } elseif (!preg_match('/^[0-9]{10}$/', preg_replace('/[^0-9]/', '', $mobile))) {
        $errors[] = "Mobile number must be 10 digits.";
    }

    if (empty($dob)) $errors[] = "Date of Birth is required.";
    if (empty($gender)) $errors[] = "Gender is required.";
    if (empty($course)) $errors[] = "Please select a Course.";
    if (empty($semester)) $errors[] = "Please select a Semester.";
    if (empty($address)) $errors[] = "Address is required.";

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check unique email
    if (empty($errors)) {
        $stmt_check = $pdo->prepare("SELECT id FROM students WHERE email = ?");
        $stmt_check->execute([$email]);
        if ($stmt_check->fetch()) {
            $errors[] = "A student with email '$email' is already registered.";
        }
    }

    // Handle Photo Upload
    $photo_filename = 'default_avatar.svg';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['photo']['tmp_name'];
        $file_name = $_FILES['photo']['name'];
        $file_size = $_FILES['photo']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

        if (!in_array($file_ext, $allowed_exts)) {
            $errors[] = "Photo format not allowed. Use JPG, PNG, WebP or SVG.";
        } elseif ($file_size > 2 * 1024 * 1024) {
            $errors[] = "Photo size must be less than 2MB.";
        } else {
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $new_name = 'student_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
            if (move_uploaded_file($file_tmp, $upload_dir . $new_name)) {
                $photo_filename = $new_name;
            }
        }
    }

    // If no errors, insert into database
    if (empty($errors)) {
        $hobbies_str = !empty($hobbies) ? implode(', ', $hobbies) : '';
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt_insert = $pdo->prepare("
                INSERT INTO students (fullname, email, mobile, dob, gender, course, semester, hobbies, address, photo, password)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt_insert->execute([
                $fullname,
                $email,
                $mobile,
                $dob,
                $gender,
                $course,
                $semester,
                $hobbies_str,
                $address,
                $photo_filename,
                $hashed_password
            ]);

            set_flash('success', "Registration completed successfully! You can now log in.");
            header("Location: login.php");
            exit;
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

$page_title = 'Student Registration Form';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            <div class="card card-custom shadow-lg">
                <div class="card-header-custom bg-primary text-white d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h4 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2"></i>Student Registration Form</h4>
                        <small class="text-white-50">Web Development using PHP (504) - Form Components</small>
                    </div>
                    <span class="badge bg-light text-primary px-3 py-2 rounded-pill fw-semibold">Phase 1 (c)</span>
                </div>

                <div class="card-body p-4 p-md-5">
                    <!-- Flash/Validation Alerts -->
                    <div id="formAlertBox"></div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                                <strong>Please correct the following errors:</strong>
                            </div>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= htmlspecialchars($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form id="studentRegistrationForm" action="register.php" method="POST" enctype="multipart/form-data" novalidate>
                        <!-- Section: Personal Details -->
                        <h5 class="fw-bold text-secondary mb-3 pb-2 border-bottom">
                            <i class="bi bi-person-lines-fill text-primary me-2"></i>Personal Information
                        </h5>

                        <div class="row g-3 mb-4">
                            <!-- 1. Full Name (Text Box) -->
                            <div class="col-md-6">
                                <label for="fullname" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="fullname" name="fullname" value="<?= htmlspecialchars($fullname) ?>" placeholder="e.g. Rahul Kumar" required>
                                </div>
                                <div class="invalid-feedback">Full name is required.</div>
                            </div>

                            <!-- 2. Email (Text Box) -->
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="e.g. rahul@example.com" required>
                                </div>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>

                            <!-- 3. Mobile No. (Text Box) -->
                            <div class="col-md-6">
                                <label for="mobile" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="tel" class="form-control" id="mobile" name="mobile" value="<?= htmlspecialchars($mobile) ?>" placeholder="e.g. 9876543210" maxlength="10" required>
                                </div>
                                <div class="invalid-feedback">Enter a valid 10-digit mobile number.</div>
                            </div>

                            <!-- 4. Date of Birth (Date Picker) -->
                            <div class="col-md-6">
                                <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                                    <input type="date" class="form-control" id="dob" name="dob" value="<?= htmlspecialchars($dob) ?>" required>
                                </div>
                                <div class="invalid-feedback">Date of birth is required.</div>
                            </div>

                            <!-- 5. Gender (Radio Button) -->
                            <div class="col-12">
                                <label class="form-label d-block">Gender <span class="text-danger">*</span></label>
                                <div id="genderContainer" class="d-flex flex-wrap gap-4 pt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender" id="genderMale" value="Male" <?= $gender === 'Male' ? 'checked' : '' ?> required>
                                        <label class="form-check-label" for="genderMale"><i class="bi bi-gender-male text-primary me-1"></i>Male</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="Female" <?= $gender === 'Female' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="genderFemale"><i class="bi bi-gender-female text-danger me-1"></i>Female</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender" id="genderOther" value="Other" <?= $gender === 'Other' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="genderOther"><i class="bi bi-gender-ambiguous text-secondary me-1"></i>Other</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Academic Details -->
                        <h5 class="fw-bold text-secondary mb-3 pb-2 border-bottom">
                            <i class="bi bi-mortarboard-fill text-primary me-2"></i>Academic Details
                        </h5>

                        <div class="row g-3 mb-4">
                            <!-- 6. Course (Drop-down List) -->
                            <div class="col-md-6">
                                <label for="course" class="form-label">Course <span class="text-danger">*</span></label>
                                <select class="form-select" id="course" name="course" required>
                                    <option value="" disabled <?= empty($course) ? 'selected' : '' ?>>-- Select Course --</option>
                                    <?php
                                    $courses = ['BCA', 'BBA', 'MCA', 'MBA', 'B.Tech (CSE)', 'B.Tech (IT)', 'B.Sc (CS)', 'M.Tech'];
                                    foreach ($courses as $c):
                                    ?>
                                        <option value="<?= $c ?>" <?= $course === $c ? 'selected' : '' ?>><?= $c ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Please select a course.</div>
                            </div>

                            <!-- 7. Semester (Drop-down List) -->
                            <div class="col-md-6">
                                <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                                <select class="form-select" id="semester" name="semester" required>
                                    <option value="" disabled <?= empty($semester) ? 'selected' : '' ?>>-- Select Semester --</option>
                                    <?php for ($i = 1; $i <= 8; $i++): $s_val = "Semester $i"; ?>
                                        <option value="<?= $s_val ?>" <?= $semester === $s_val ? 'selected' : '' ?>><?= $s_val ?></option>
                                    <?php endfor; ?>
                                </select>
                                <div class="invalid-feedback">Please select a semester.</div>
                            </div>

                            <!-- 8. Hobbies (Checkbox) -->
                            <div class="col-12">
                                <label class="form-label d-block">Hobbies</label>
                                <div class="d-flex flex-wrap gap-4 pt-1">
                                    <?php
                                    $available_hobbies = ['Reading', 'Sports', 'Music', 'Coding', 'Other'];
                                    foreach ($available_hobbies as $hob):
                                        $checked = in_array($hob, (array)$hobbies) ? 'checked' : '';
                                    ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="hobbies[]" id="hob_<?= strtolower($hob) ?>" value="<?= $hob ?>" <?= $checked ?>>
                                        <label class="form-check-label" for="hob_<?= strtolower($hob) ?>"><?= $hob ?></label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Contact & Photo -->
                        <h5 class="fw-bold text-secondary mb-3 pb-2 border-bottom">
                            <i class="bi bi-geo-alt-fill text-primary me-2"></i>Address & Profile Photo
                        </h5>

                        <div class="row g-3 mb-4">
                            <!-- 9. Address (Text Area) -->
                            <div class="col-md-8">
                                <label for="address" class="form-label">Residential Address <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="address" name="address" rows="4" placeholder="Enter complete address, city, state, pin code..." required><?= htmlspecialchars($address) ?></textarea>
                                <div class="invalid-feedback">Residential address is required.</div>
                            </div>

                            <!-- 10. Upload Photo (File Upload) -->
                            <div class="col-md-4 text-center">
                                <label for="photo" class="form-label d-block text-start">Upload Photo</label>
                                <div class="mb-2">
                                    <img id="photoPreview" src="uploads/default_avatar.svg" alt="Preview" class="rounded-circle border shadow-sm" style="width: 90px; height: 90px; object-fit: cover;">
                                </div>
                                <input class="form-control form-control-sm" type="file" id="photo" name="photo" accept="image/*">
                                <div class="form-text small">JPG, PNG, WebP (Max 2MB)</div>
                            </div>
                        </div>

                        <!-- Section: Security / Password -->
                        <h5 class="fw-bold text-secondary mb-3 pb-2 border-bottom">
                            <i class="bi bi-shield-lock-fill text-primary me-2"></i>Account Security
                        </h5>

                        <div class="row g-3 mb-4">
                            <!-- 11. Password (Password) -->
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Create password (min 6 characters)" required>
                                <div class="invalid-feedback">Password must be at least 6 characters.</div>
                            </div>

                            <!-- 12. Confirm Password (Password) -->
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
                                <div class="invalid-feedback">Passwords do not match.</div>
                            </div>
                        </div>

                        <!-- Action Buttons (Submit, Reset) -->
                        <div class="d-flex flex-wrap gap-3 justify-content-end pt-3 border-top">
                            <button type="reset" id="resetBtn" class="btn btn-outline-secondary px-4 py-2 rounded-3">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-success px-5 py-2 rounded-3 fw-semibold shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> Register Student
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
