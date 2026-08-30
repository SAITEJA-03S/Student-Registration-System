/**
 * Student Registration System - Interactive Client App (GitHub Pages Edition)
 */

// Initial Seed Data
const INITIAL_STUDENTS = [
    {
        id: 1,
        fullname: "Rahul Kumar",
        email: "rahul@example.com",
        mobile: "9876543210",
        dob: "2003-05-22",
        gender: "Male",
        course: "BCA",
        semester: "Semester 5",
        hobbies: "Reading, Sports",
        address: "123, New Colony, Bhopal, Madhya Pradesh",
        photo: "assets/img/default_avatar.svg",
        created_at: "2026-08-20 10:30:00"
    },
    {
        id: 2,
        fullname: "Priya Sharma",
        email: "priya@example.com",
        mobile: "9822334455",
        dob: "2002-11-14",
        gender: "Female",
        course: "BBA",
        semester: "Semester 3",
        hobbies: "Music, Reading",
        address: "45 Park Avenue, Indore, MP",
        photo: "assets/img/default_avatar.svg",
        created_at: "2026-08-21 11:15:00"
    },
    {
        id: 3,
        fullname: "Aman Verma",
        email: "aman@example.com",
        mobile: "9687452012",
        dob: "2001-08-19",
        gender: "Male",
        course: "MCA",
        semester: "Semester 2",
        hobbies: "Sports, Coding",
        address: "78 Sector 9, Gwalior, MP",
        photo: "assets/img/default_avatar.svg",
        created_at: "2026-08-22 09:40:00"
    },
    {
        id: 4,
        fullname: "Neha Singh",
        email: "neha@example.com",
        mobile: "9712345678",
        dob: "2003-02-10",
        gender: "Female",
        course: "BCA",
        semester: "Semester 4",
        hobbies: "Music, Other",
        address: "89 Lake View Road, Jabalpur, MP",
        photo: "assets/img/default_avatar.svg",
        created_at: "2026-08-23 14:20:00"
    },
    {
        id: 5,
        fullname: "Vikram Patel",
        email: "vikram@example.com",
        mobile: "9898989898",
        dob: "2002-04-15",
        gender: "Male",
        course: "B.Tech (CSE)",
        semester: "Semester 6",
        hobbies: "Reading, Coding",
        address: "12 Tech Hub, Bhopal, MP",
        photo: "assets/img/default_avatar.svg",
        created_at: "2026-08-24 16:50:00"
    },
    {
        id: 6,
        fullname: "Ananya Roy",
        email: "ananya@example.com",
        mobile: "9777888999",
        dob: "2004-09-25",
        gender: "Female",
        course: "BBA",
        semester: "Semester 1",
        hobbies: "Reading, Music",
        address: "55 Green Meadows, Ujjain, MP",
        photo: "assets/img/default_avatar.svg",
        created_at: "2026-08-25 12:10:00"
    }
];

// App State
let students = [];
let currentUser = null;
let currentPage = 1;
const PAGE_LIMIT = 5;
let studentToDeleteId = null;
let currentViewingStudent = null;
let courseChartInstance = null;
let semesterChartInstance = null;

// Initialize App
document.addEventListener('DOMContentLoaded', () => {
    // Load data from LocalStorage
    const stored = localStorage.getItem('srs_students');
    if (stored) {
        try {
            students = JSON.parse(stored);
        } catch (e) {
            students = INITIAL_STUDENTS;
        }
    } else {
        students = [...INITIAL_STUDENTS];
        saveStudents();
    }

    // Check existing session
    const savedUser = localStorage.getItem('srs_user');
    if (savedUser) {
        currentUser = JSON.parse(savedUser);
    }

    // Update Year
    const yearEl = document.getElementById('currentYear');
    if (yearEl) yearEl.textContent = new Date().getFullYear();

    // Start Live Clock
    startLiveClock();

    // Render Initial View
    if (currentUser) {
        navigateTo('dashboard');
    } else {
        navigateTo('login');
    }
});

function saveStudents() {
    localStorage.setItem('srs_students', JSON.stringify(students));
}

// 1. Live Clock for Login & System (Phase 1 a)
function startLiveClock() {
    function tick() {
        const now = new Date();
        const dateOptions = { day: '2-digit', month: 'short', year: 'numeric' };
        const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };

        const dateStr = now.toLocaleDateString('en-GB', dateOptions);
        const timeStr = now.toLocaleTimeString('en-US', timeOptions);

        const dEl = document.getElementById('liveDateDisplay');
        const tEl = document.getElementById('liveTimeDisplay');
        const pTs = document.getElementById('printTimestamp');
        const rSub = document.getElementById('reportPrintSub');

        if (dEl) dEl.textContent = dateStr;
        if (tEl) tEl.textContent = timeStr;
        if (pTs) pTs.textContent = `Date Generated: ${dateStr}, ${timeStr}`;
        if (rSub) rSub.textContent = `Generated On: ${dateStr}, ${timeStr}`;
    }
    tick();
    setInterval(tick, 1000);
}

// 2. Navigation Router
function navigateTo(viewName) {
    // Hide all views
    document.querySelectorAll('.page-view').forEach(v => v.classList.add('d-none'));

    // Update nav links
    document.querySelectorAll('.nav-btn').forEach(btn => {
        if (btn.dataset.page === viewName) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    renderNavbar();

    // Show selected view
    switch (viewName) {
        case 'login':
            document.getElementById('viewLogin').classList.remove('d-none');
            break;
        case 'dashboard':
            if (!currentUser) { navigateTo('login'); return; }
            document.getElementById('viewDashboard').classList.remove('d-none');
            renderDashboard();
            break;
        case 'students':
            if (!currentUser) { navigateTo('login'); return; }
            document.getElementById('viewStudents').classList.remove('d-none');
            renderStudentsTable();
            break;
        case 'register':
        case 'add-student':
            document.getElementById('viewForm').classList.remove('d-none');
            break;
        case 'profile':
            document.getElementById('viewProfile').classList.remove('d-none');
            break;
        case 'reports':
            if (!currentUser) { navigateTo('login'); return; }
            document.getElementById('viewReports').classList.remove('d-none');
            renderReports();
            break;
    }
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// 3. Navbar rendering based on auth
function renderNavbar() {
    const navLinksAuth = document.getElementById('navLinksAuth');
    const navUserArea = document.getElementById('navUserArea');

    if (currentUser) {
        if (navLinksAuth) navLinksAuth.style.display = 'flex';
        if (navUserArea) {
            navUserArea.innerHTML = `
                <div class="text-light d-none d-md-block text-end">
                    <div class="small fw-semibold text-white">${escapeHtml(currentUser.username)}</div>
                    <div class="text-white-50" style="font-size: 0.75rem;">${escapeHtml(currentUser.role.toUpperCase())}</div>
                </div>
                <button class="btn btn-danger btn-sm px-3 rounded-pill d-inline-flex align-items-center gap-1" onclick="handleLogout()">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            `;
        }
    } else {
        if (navLinksAuth) navLinksAuth.style.display = 'none';
        if (navUserArea) {
            navUserArea.innerHTML = `
                <button class="btn btn-outline-light btn-sm px-3 rounded-pill" onclick="navigateTo('login')">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Login
                </button>
                <button class="btn btn-primary btn-sm px-3 rounded-pill" onclick="openRegisterPublic()">
                    <i class="bi bi-pencil-square me-1"></i> Register Student
                </button>
            `;
        }
    }
}

// 4. Authentication Handlers (Phase 1 b)
function handleLogin(e) {
    e.preventDefault();
    const userVal = document.getElementById('loginUsername').value.trim();
    const passVal = document.getElementById('loginPassword').value.trim();
    const alertEl = document.getElementById('loginAlert');
    const alertMsg = document.getElementById('loginAlertMsg');

    if (userVal === 'admin' && passVal === 'admin123') {
        currentUser = { username: 'Admin', role: 'admin' };
        localStorage.setItem('srs_user', JSON.stringify(currentUser));
        alertEl.classList.add('d-none');
        showToast('Login successful! Welcome Admin.');
        navigateTo('dashboard');
        return;
    }

    // Student Login
    const st = students.find(s => s.email.toLowerCase() === userVal.toLowerCase());
    if (st && (passVal === 'student123' || passVal === 'admin123')) {
        currentUser = { username: st.fullname, role: 'student', studentId: st.id };
        localStorage.setItem('srs_user', JSON.stringify(currentUser));
        alertEl.classList.add('d-none');
        showToast(`Welcome back, ${st.fullname}!`);
        navigateTo('dashboard');
        return;
    }

    // Error
    alertEl.classList.remove('d-none');
    alertMsg.textContent = 'Invalid Username or Password!';
}

function handleLogout() {
    currentUser = null;
    localStorage.removeItem('srs_user');
    showToast('You have been logged out successfully.', 'info');
    navigateTo('login');
}

// 5. Dashboard View (Phase 2)
function renderDashboard() {
    document.getElementById('dashUserGreeting').textContent = currentUser ? currentUser.username : 'User';

    const total = students.length;
    const courses = [...new Set(students.map(s => s.course))].length;
    const male = students.filter(s => s.gender === 'Male').length;
    const female = students.filter(s => s.gender === 'Female').length;

    document.getElementById('statTotalStudents').textContent = total;
    document.getElementById('statActiveCourses').textContent = courses;
    document.getElementById('statMaleStudents').textContent = male;
    document.getElementById('statFemaleStudents').textContent = female;

    // Recent 5
    const recent = [...students].slice(-5).reverse();
    const tbody = document.getElementById('dashRecentStudentsTable');
    if (tbody) {
        if (recent.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted">No student records found.</td></tr>`;
        } else {
            tbody.innerHTML = recent.map(st => `
                <tr>
                    <td class="fw-bold text-muted">#${st.id}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="${st.photo || 'assets/img/default_avatar.svg'}" class="student-avatar-thumb" style="width: 36px; height: 36px;">
                            <div>
                                <div class="fw-bold text-dark">${escapeHtml(st.fullname)}</div>
                                <small class="text-muted">${escapeHtml(st.gender)}</small>
                            </div>
                        </div>
                    </td>
                    <td>${escapeHtml(st.email)}</td>
                    <td class="font-monospace small">${escapeHtml(st.mobile)}</td>
                    <td><span class="badge bg-primary-subtle text-primary fw-semibold">${escapeHtml(st.course)}</span></td>
                    <td>${escapeHtml(st.semester)}</td>
                    <td class="text-center">
                        <button class="btn btn-outline-info btn-action" onclick="viewProfile(${st.id})" title="View"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-outline-primary btn-action" onclick="editStudent(${st.id})" title="Edit"><i class="bi bi-pencil-square"></i></button>
                    </td>
                </tr>
            `).join('');
        }
    }

    // Render Charts
    renderDashboardCharts();
}

function renderDashboardCharts() {
    // Course Count
    const courseMap = {};
    students.forEach(s => { courseMap[s.course] = (courseMap[s.course] || 0) + 1; });
    const courseLabels = Object.keys(courseMap);
    const courseData = Object.values(courseMap);

    const ctxC = document.getElementById('dashCourseChart');
    if (ctxC) {
        if (courseChartInstance) courseChartInstance.destroy();
        courseChartInstance = new Chart(ctxC, {
            type: 'doughnut',
            data: {
                labels: courseLabels,
                datasets: [{
                    data: courseData,
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16', '#64748b'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right' } }
            }
        });
    }

    // Semester Count
    const semMap = {};
    students.forEach(s => { semMap[s.semester] = (semMap[s.semester] || 0) + 1; });
    const semLabels = Object.keys(semMap).sort();
    const semData = semLabels.map(k => semMap[k]);

    const ctxS = document.getElementById('dashSemesterChart');
    if (ctxS) {
        if (semesterChartInstance) semesterChartInstance.destroy();
        semesterChartInstance = new Chart(ctxS, {
            type: 'bar',
            data: {
                labels: semLabels,
                datasets: [{
                    label: 'Students Enrolled',
                    data: semData,
                    backgroundColor: 'rgba(59, 130, 246, 0.85)',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                plugins: { legend: { display: false } }
            }
        });
    }
}

// 6. Student Management Table with Search & Pagination (Phase 1 e & f)
function renderStudentsTable() {
    const search = (document.getElementById('searchInput')?.value || '').toLowerCase().trim();
    const courseFilter = document.getElementById('courseFilterSelect')?.value || '';

    let filtered = students.filter(st => {
        const matchesSearch = !search || 
            st.fullname.toLowerCase().includes(search) || 
            st.email.toLowerCase().includes(search) || 
            st.mobile.includes(search) || 
            st.course.toLowerCase().includes(search) ||
            st.address.toLowerCase().includes(search);
        const matchesCourse = !courseFilter || st.course === courseFilter;
        return matchesSearch && matchesCourse;
    });

    const total = filtered.length;
    const totalPages = Math.ceil(total / PAGE_LIMIT) || 1;
    if (currentPage > totalPages) currentPage = totalPages;

    const startIdx = (currentPage - 1) * PAGE_LIMIT;
    const paginated = filtered.slice(startIdx, startIdx + PAGE_LIMIT);

    // Summary badge & text
    const totalBadge = document.getElementById('studentsTotalBadge');
    if (totalBadge) totalBadge.textContent = `${total} Total`;

    const summaryText = document.getElementById('studentsPaginationSummary');
    if (summaryText) {
        summaryText.textContent = total > 0 ? `Showing ${startIdx + 1} to ${Math.min(startIdx + PAGE_LIMIT, total)} of ${total} entries` : 'No records';
    }

    const pageIndicator = document.getElementById('paginationPageIndicator');
    if (pageIndicator) pageIndicator.textContent = `Page ${currentPage} of ${totalPages}`;

    const tbody = document.getElementById('studentsTableBody');
    if (!tbody) return;

    if (paginated.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                    <h6>No student records found</h6>
                    <p class="small mb-3">Try adjusting your search criteria or register a new student.</p>
                    <button class="btn btn-sm btn-primary rounded-pill px-3" onclick="openAddStudent()">Add New Student</button>
                </td>
            </tr>
        `;
    } else {
        tbody.innerHTML = paginated.map(st => `
            <tr>
                <td class="fw-bold text-muted">#${st.id}</td>
                <td>
                    <img src="${st.photo || 'assets/img/default_avatar.svg'}" class="student-avatar-thumb shadow-sm">
                </td>
                <td>
                    <div class="fw-semibold text-dark">${escapeHtml(st.fullname)}</div>
                    <span class="badge bg-secondary-subtle text-secondary small">${escapeHtml(st.gender)}</span>
                </td>
                <td><a href="mailto:${escapeHtml(st.email)}" class="text-decoration-none text-muted">${escapeHtml(st.email)}</a></td>
                <td><span class="font-monospace">${escapeHtml(st.mobile)}</span></td>
                <td><span class="badge bg-info-subtle text-info-emphasis px-2 py-1 fw-bold">${escapeHtml(st.course)}</span></td>
                <td>${escapeHtml(st.semester)}</td>
                <td class="small text-muted">${formatDate(st.dob)}</td>
                <td class="text-center">
                    <div class="btn-group" role="group">
                        <button class="btn btn-outline-info btn-action" onclick="viewProfile(${st.id})" title="View Profile"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-outline-primary btn-action" onclick="editStudent(${st.id})" title="Edit Record"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn btn-outline-danger btn-action" onclick="promptDelete(${st.id})" title="Delete Student"><i class="bi bi-trash3"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    // Pagination Controls
    renderPaginationControls(totalPages);
}

function renderPaginationControls(totalPages) {
    const controls = document.getElementById('paginationControls');
    if (!controls) return;

    if (totalPages <= 1) {
        controls.innerHTML = '';
        return;
    }

    let html = `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">&laquo;</a>
        </li>
    `;

    for (let i = 1; i <= totalPages; i++) {
        html += `
            <li class="page-item ${currentPage === i ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
            </li>
        `;
    }

    html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">&raquo;</a>
        </li>
    `;

    controls.innerHTML = html;
}

function changePage(page) {
    currentPage = page;
    renderStudentsTable();
}

function clearFilters() {
    if (document.getElementById('searchInput')) document.getElementById('searchInput').value = '';
    if (document.getElementById('courseFilterSelect')) document.getElementById('courseFilterSelect').value = '';
    currentPage = 1;
    renderStudentsTable();
}

// 7. Form Operations (Add / Edit / Submit) with Validation (Phase 1 c & d)
function openAddStudent() {
    resetStudentForm();
    document.getElementById('formHeaderTitle').innerHTML = '<i class="bi bi-person-plus me-2"></i>Add New Student';
    document.getElementById('formSubmitBtn').innerHTML = '<i class="bi bi-check2-circle me-1"></i> Save Student';
    document.getElementById('editStudentId').value = '';
    navigateTo('add-student');
}

function openRegisterPublic() {
    resetStudentForm();
    document.getElementById('formHeaderTitle').innerHTML = '<i class="bi bi-person-badge me-2"></i>Student Registration Form';
    document.getElementById('formSubmitBtn').innerHTML = '<i class="bi bi-check2-circle me-1"></i> Register Student';
    document.getElementById('editStudentId').value = '';
    navigateTo('register');
}

function editStudent(id) {
    const st = students.find(s => s.id === id);
    if (!st) return;

    resetStudentForm();
    document.getElementById('formHeaderTitle').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Edit Student Information';
    document.getElementById('formSubmitBtn').innerHTML = '<i class="bi bi-check2-circle me-1"></i> Save Changes';
    document.getElementById('editStudentId').value = st.id;

    document.getElementById('formFullname').value = st.fullname;
    document.getElementById('formEmail').value = st.email;
    document.getElementById('formMobile').value = st.mobile;
    document.getElementById('formDob').value = st.dob;
    document.getElementById('formCourse').value = st.course;
    document.getElementById('formSemester').value = st.semester;
    document.getElementById('formAddress').value = st.address;

    // Gender
    const genderRad = document.querySelector(`input[name="gender"][value="${st.gender}"]`);
    if (genderRad) genderRad.checked = true;

    // Hobbies
    const hobs = st.hobbies ? st.hobbies.split(',').map(h => h.trim()) : [];
    document.querySelectorAll('.hobby-cb').forEach(cb => {
        cb.checked = hobs.includes(cb.value);
    });

    // Photo
    if (st.photo) {
        document.getElementById('formPhotoPreview').src = st.photo;
    }

    navigateTo('add-student');
}

function handleFormSubmit(e) {
    e.preventDefault();
    const alertBox = document.getElementById('formAlertBox');
    alertBox.innerHTML = '';

    const idVal = document.getElementById('editStudentId').value;
    const fullname = document.getElementById('formFullname').value.trim();
    const email = document.getElementById('formEmail').value.trim();
    const mobile = document.getElementById('formMobile').value.trim();
    const dob = document.getElementById('formDob').value;
    const gender = document.querySelector('input[name="gender"]:checked')?.value || 'Male';
    const course = document.getElementById('formCourse').value;
    const semester = document.getElementById('formSemester').value;
    const address = document.getElementById('formAddress').value.trim();

    const selectedHobbies = Array.from(document.querySelectorAll('.hobby-cb:checked')).map(cb => cb.value).join(', ');
    const photoSrc = document.getElementById('formPhotoPreview').src;

    let errors = [];

    if (!fullname || fullname.length < 3) errors.push('Full name is required (at least 3 characters).');
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.push('A valid email address is required.');
    if (!mobile || !/^[0-9]{10}$/.test(mobile.replace(/[^0-9]/g, ''))) errors.push('Mobile number must be exactly 10 digits.');
    if (!dob) errors.push('Date of Birth is required.');
    if (!course) errors.push('Please select a course.');
    if (!semester) errors.push('Please select a semester.');
    if (!address) errors.push('Residential address is required.');

    // Unique email check
    const existing = students.find(s => s.email.toLowerCase() === email.toLowerCase() && s.id !== parseInt(idVal));
    if (existing) errors.push(`The email '${email}' is already registered.`);

    if (errors.length > 0) {
        alertBox.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Please fix the errors:</strong>
                <ul class="mb-0 ps-3 mt-1">${errors.map(err => `<li>${escapeHtml(err)}</li>`).join('')}</ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    if (idVal) {
        // Update existing
        const idx = students.findIndex(s => s.id === parseInt(idVal));
        if (idx !== -1) {
            students[idx] = {
                ...students[idx],
                fullname, email, mobile, dob, gender, course, semester,
                hobbies: selectedHobbies,
                address,
                photo: photoSrc
            };
            saveStudents();
            showToast('Student record updated successfully!');
            navigateTo('students');
        }
    } else {
        // Insert new
        const newId = students.length > 0 ? Math.max(...students.map(s => s.id)) + 1 : 1;
        const newStudent = {
            id: newId,
            fullname, email, mobile, dob, gender, course, semester,
            hobbies: selectedHobbies,
            address,
            photo: photoSrc,
            created_at: new Date().toISOString().replace('T', ' ').substring(0, 19)
        };
        students.push(newStudent);
        saveStudents();
        showToast('Student registered successfully!');
        if (currentUser) {
            navigateTo('students');
        } else {
            navigateTo('login');
        }
    }
}

function resetStudentForm() {
    const form = document.getElementById('studentRegistrationForm');
    if (form) form.reset();
    document.getElementById('formPhotoPreview').src = 'assets/img/default_avatar.svg';
    document.getElementById('formAlertBox').innerHTML = '';
}

function previewPhoto(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            alert('File size exceeds 2MB limit!');
            e.target.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = (event) => {
            document.getElementById('formPhotoPreview').src = event.target.result;
        };
        reader.readAsDataURL(file);
    }
}

// 8. Delete Student
function promptDelete(id) {
    const st = students.find(s => s.id === id);
    if (!st) return;
    studentToDeleteId = id;
    document.getElementById('deleteStudentModalName').textContent = st.fullname;
    const modalEl = document.getElementById('deleteConfirmModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function executeDelete() {
    if (studentToDeleteId !== null) {
        students = students.filter(s => s.id !== studentToDeleteId);
        saveStudents();
        const modalEl = document.getElementById('deleteConfirmModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
        studentToDeleteId = null;
        showToast('Student deleted successfully.');
        renderStudentsTable();
    }
}

// 9. Profile & ID Card View
function viewProfile(id) {
    const st = students.find(s => s.id === id);
    if (!st) return;
    currentViewingStudent = st;

    document.getElementById('profileFullname').textContent = st.fullname;
    document.getElementById('profileId').textContent = '#' + String(st.id).padStart(5, '0');
    document.getElementById('profileCourse').textContent = st.course;
    document.getElementById('profileSemester').textContent = st.semester;
    document.getElementById('profileGender').textContent = st.gender;
    document.getElementById('profileDob').textContent = formatDate(st.dob);
    document.getElementById('profileMobile').textContent = st.mobile;
    document.getElementById('profileEmail').textContent = st.email;
    document.getElementById('profileHobbies').textContent = st.hobbies || 'None specified';
    document.getElementById('profileAddress').textContent = st.address;
    document.getElementById('profilePhoto').src = st.photo || 'assets/img/default_avatar.svg';

    navigateTo('profile');
}

function editCurrentProfile() {
    if (currentViewingStudent) {
        editStudent(currentViewingStudent.id);
    }
}

// 10. Reports View & CSV Export (Phase 1 g)
function renderReports() {
    const cFilter = document.getElementById('repCourseFilter')?.value || '';
    const sFilter = document.getElementById('repSemFilter')?.value || '';
    const gFilter = document.getElementById('repGenderFilter')?.value || '';

    const matched = students.filter(s => {
        return (!cFilter || s.course === cFilter) &&
               (!sFilter || s.semester === sFilter) &&
               (!gFilter || s.gender === gFilter);
    });

    document.getElementById('repTotalMatched').textContent = matched.length;
    document.getElementById('repRecordsBadge').textContent = `${matched.length} Records Found`;

    const male = matched.filter(s => s.gender === 'Male').length;
    const female = matched.filter(s => s.gender === 'Female').length;
    const other = matched.filter(s => s.gender === 'Other').length;
    document.getElementById('repGenderBreakdown').textContent = `Male: ${male} | Female: ${female} | Other: ${other}`;

    const courses = [...new Set(matched.map(s => s.course))].length;
    document.getElementById('repCoursesCount').textContent = courses;

    const tbody = document.getElementById('reportTableBody');
    if (!tbody) return;

    if (matched.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center py-4 text-muted">No student records match the selected report filter.</td></tr>`;
    } else {
        tbody.innerHTML = matched.map((st, idx) => `
            <tr>
                <td class="text-center font-monospace">${idx + 1}</td>
                <td class="fw-bold">${escapeHtml(st.fullname)}</td>
                <td>${escapeHtml(st.email)}</td>
                <td class="font-monospace">${escapeHtml(st.mobile)}</td>
                <td>${escapeHtml(st.gender)}</td>
                <td><span class="badge bg-primary text-white">${escapeHtml(st.course)}</span></td>
                <td>${escapeHtml(st.semester)}</td>
                <td>${formatDate(st.dob)}</td>
                <td class="small">${escapeHtml(st.address)}</td>
            </tr>
        `).join('');
    }
}

function clearReportFilters() {
    if (document.getElementById('repCourseFilter')) document.getElementById('repCourseFilter').value = '';
    if (document.getElementById('repSemFilter')) document.getElementById('repSemFilter').value = '';
    if (document.getElementById('repGenderFilter')) document.getElementById('repGenderFilter').value = '';
    renderReports();
}

function exportCSV() {
    let csv = "\uFEFF"; // UTF-8 BOM
    csv += "ID,Full Name,Email Address,Mobile Number,Date of Birth,Gender,Course,Semester,Hobbies,Address,Registration Date\n";

    students.forEach(st => {
        const row = [
            st.id,
            `"${st.fullname.replace(/"/g, '""')}"`,
            `"${st.email.replace(/"/g, '""')}"`,
            `"${st.mobile}"`,
            `"${st.dob}"`,
            `"${st.gender}"`,
            `"${st.course}"`,
            `"${st.semester}"`,
            `"${(st.hobbies || '').replace(/"/g, '""')}"`,
            `"${(st.address || '').replace(/"/g, '""')}"`,
            `"${st.created_at || ''}"`
        ];
        csv += row.join(",") + "\n";
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", `student_report_${new Date().toISOString().slice(0,10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    showToast('CSV export downloaded successfully!');
}

// Helpers
function showToast(msg, type = 'success') {
    const toastEl = document.getElementById('liveToast');
    const msgEl = document.getElementById('toastMessage');
    if (!toastEl || !msgEl) return;

    msgEl.textContent = msg;
    toastEl.className = `toast align-items-center text-white bg-${type} border-0 shadow-lg`;
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}

function formatDate(dateStr) {
    if (!dateStr) return '--';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
