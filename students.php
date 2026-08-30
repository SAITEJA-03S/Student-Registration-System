<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
check_auth();

// Search & Filter Parameters
$search = trim($_GET['search'] ?? '');
$course_filter = trim($_GET['course'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 5; // Records per page
$offset = ($page - 1) * $limit;

// Build Query
$where_clauses = [];
$params = [];

if (!empty($search)) {
    $where_clauses[] = "(fullname LIKE ? OR email LIKE ? OR mobile LIKE ? OR course LIKE ? OR address LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term, $search_term]);
}

if (!empty($course_filter)) {
    $where_clauses[] = "course = ?";
    $params[] = $course_filter;
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Count Total Records
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM students $where_sql");
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Fetch Paginated Records
$data_stmt = $pdo->prepare("SELECT * FROM students $where_sql ORDER BY id DESC LIMIT $limit OFFSET $offset");
$data_stmt->execute($params);
$students = $data_stmt->fetchAll(PDO::FETCH_ASSOC);

$flash_success = get_flash('success');
$flash_error = get_flash('error');

$page_title = 'Student List & Records';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    <!-- Header & Breadcrumb -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-people-fill text-primary me-2"></i>Student Management</h3>
            <p class="text-muted small mb-0">Phase 1 (f) - Retrieve Data in Table &amp; Paging Navigation</p>
        </div>
        <div class="d-flex gap-2">
            <a href="reports.php" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-file-earmark-bar-graph me-1"></i> Reports
            </a>
            <a href="export.php?format=csv<?= !empty($course_filter) ? '&course=' . urlencode($course_filter) : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="btn btn-outline-success rounded-pill px-3">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
            </a>
            <a href="student_add.php" class="btn btn-primary rounded-pill px-3 shadow-sm">
                <i class="bi bi-person-plus me-1"></i> Add Student
            </a>
        </div>
    </div>

    <?php if ($flash_success): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($flash_success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($flash_error): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($flash_error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Search & Filter Card -->
    <div class="card card-custom mb-4 shadow-sm border-0">
        <div class="card-body p-3">
            <form action="students.php" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by name, email, mobile, course..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="course" class="form-select">
                        <option value="">-- Filter by All Courses --</option>
                        <?php
                        $courses = ['BCA', 'BBA', 'MCA', 'MBA', 'B.Tech (CSE)', 'B.Tech (IT)', 'B.Sc (CS)', 'M.Tech'];
                        foreach ($courses as $c):
                        ?>
                            <option value="<?= $c ?>" <?= $course_filter === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i> Apply Filter</button>
                    <?php if (!empty($search) || !empty($course_filter)): ?>
                        <a href="students.php" class="btn btn-outline-secondary" title="Reset Filters"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Student Table Card -->
    <div class="card card-custom shadow-sm border-0 overflow-hidden">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-secondary">
                Registered Students <span class="badge bg-primary-subtle text-primary ms-2"><?= $total_records ?> Total</span>
            </h6>
            <span class="small text-muted">Showing <?= min($offset + 1, $total_records) ?> to <?= min($offset + $limit, $total_records) ?> of <?= $total_records ?> entries</span>
        </div>
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th style="width: 70px;">Photo</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Course</th>
                        <th>Semester</th>
                        <th>DOB</th>
                        <th class="text-center" style="width: 150px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $row): 
                            $photo_src = 'uploads/' . ($row['photo'] ? $row['photo'] : 'default_avatar.svg');
                            if (!file_exists($photo_src)) {
                                $photo_src = 'uploads/default_avatar.svg';
                            }
                        ?>
                        <tr>
                            <td class="fw-bold text-muted">#<?= $row['id'] ?></td>
                            <td>
                                <img src="<?= htmlspecialchars($photo_src) ?>" alt="Photo" class="student-avatar-thumb shadow-sm">
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($row['fullname']) ?></div>
                                <span class="badge bg-secondary-subtle text-secondary small"><?= htmlspecialchars($row['gender']) ?></span>
                            </td>
                            <td><a href="mailto:<?= htmlspecialchars($row['email']) ?>" class="text-decoration-none text-muted"><?= htmlspecialchars($row['email']) ?></a></td>
                            <td><span class="font-monospace"><?= htmlspecialchars($row['mobile']) ?></span></td>
                            <td><span class="badge bg-info-subtle text-info-emphasis px-2 py-1 fw-bold"><?= htmlspecialchars($row['course']) ?></span></td>
                            <td><?= htmlspecialchars($row['semester']) ?></td>
                            <td class="small text-muted"><?= date('d M Y', strtotime($row['dob'])) ?></td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="student_view.php?id=<?= $row['id'] ?>" class="btn btn-outline-info btn-action" title="View Profile">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="student_edit.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-action" title="Edit Record">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger btn-action" title="Delete Student" onclick="confirmDelete(<?= $row['id'] ?>, '<?= addslashes(htmlspecialchars($row['fullname'])) ?>')">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                <h6>No student records found</h6>
                                <p class="small mb-3">Try adjusting your search criteria or register a new student.</p>
                                <a href="student_add.php" class="btn btn-sm btn-primary rounded-pill px-3">Add New Student</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paging Navigation (Phase 1 f Requirement) -->
        <?php if ($total_pages > 1): ?>
        <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-3">
            <span class="small text-muted">Page <?= $page ?> of <?= $total_pages ?></span>
            <nav aria-label="Student Table Pagination">
                <ul class="pagination pagination-sm mb-0">
                    <!-- Previous Button -->
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&course=<?= urlencode($course_filter) ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    <!-- Page Numbers -->
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&course=<?= urlencode($course_filter) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <!-- Next Button -->
                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&course=<?= urlencode($course_filter) ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="fs-5 mb-1">Are you sure you want to delete student:</p>
                <h5 class="fw-bold text-danger" id="deleteStudentName"></h5>
                <p class="text-muted small mt-2">This action cannot be undone. All related data will be removed.</p>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="deleteConfirmBtn" class="btn btn-danger rounded-pill px-4">Yes, Delete</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
