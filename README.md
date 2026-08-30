# Student Registration System
**Web Development using PHP (504)**

[![GitHub Pages](https://img.shields.io/badge/Live%20Demo-GitHub%20Pages-brightgreen?style=for-the-badge&logo=github)](https://saiteja-03s.github.io/Student-Registration-System/)

🌐 **Live Demo Website**: [https://saiteja-03s.github.io/Student-Registration-System/](https://saiteja-03s.github.io/Student-Registration-System/)

A full-featured, secure, and modern Student Registration and Management System built with **PHP, MySQL, JavaScript, HTML5, CSS3, and Bootstrap 5**.

---

## 📌 Features & Highlights (Phase 1 & Phase 2 Specifications)

### 1. User Authentication (Phase 1 a & b)
- **Live Date & Time Clock**: Dynamically updates timestamp on the login screen.
- **Role-Based Access**: Administrative dashboard & Student portal.
- **Appropriate Error Handling**: Instant error badges for invalid username/password combinations.
- **Default Credentials**:
  - **Admin**: `admin` / `admin123`
  - **Student**: `rahul@example.com` / `student123`

### 2. Comprehensive Registration Form (Phase 1 c)
Includes **all** required form controls:
- **Text Box**: Full Name, Email, Mobile Number
- **Date Picker**: Date of Birth
- **Radio Buttons**: Gender selection (Male, Female, Other)
- **Drop-down Menus**: Course (`BCA`, `BBA`, `MCA`, `B.Tech`, etc.) & Semester (`Semester 1` to `8`)
- **Checkboxes**: Hobbies (`Reading`, `Sports`, `Music`, `Coding`, `Other`)
- **Text Area**: Residential Address
- **File Upload**: Profile photo upload with instant image preview
- **Password Fields**: Secure password & confirm password verification
- **Buttons**: Submit / Register & Form Reset

### 3. Validation Suite (Phase 1 d)
- Client-side validation using **JavaScript**:
  - Non-empty field enforcement
  - Email regex validation
  - 10-digit mobile number validation
  - Password length & matching verification
  - Photo file type & size checking (< 2MB)
- Server-side validation using **PHP** with sanitized prepared statements (SQL injection protection & XSS prevention).

### 4. Student Management & CRUD (Phase 1 e & f)
- **Insert**: Register new students publicly or from the admin portal.
- **Read / View**: Interactive table with profile avatars, quick info, and printable Student ID cards.
- **Update**: Edit all student parameters with pre-filled inputs.
- **Delete**: Soft/hard deletion with modal confirmation.
- **Live Search & Filter**: Search by name, email, mobile, or filter by course.
- **Pagination**: Numbered pagination (`« 1 2 3 »`) for large datasets.

### 5. Analytics & Report Generation (Phase 1 g & Phase 2)
- **Dashboard Metrics**: Total students, active courses, gender ratios.
- **Interactive Charts**: Chart.js visualizations (Course doughnut chart & Semester bar chart).
- **Printable Layout**: Clean print stylesheet (`@media print`) for hard-copy generation and PDF export.
- **CSV / Excel Export**: One-click download of student records with active filters.

---

## 🗂️ Project Structure

```text
STD REG SYS/
├── assets/
│   ├── css/
│   │   └── style.css            # Custom CSS & print styles
│   └── js/
│       ├── app.js               # UI helpers & modal handlers
│       ├── clock.js             # Live date/time clock for login
│       └── validation.js        # Form validation scripts
├── config/
│   └── db.php                   # Database connection (MySQL + SQLite fallback)
├── database/
│   ├── schema.sql               # MySQL DDL table schema
│   ├── seed.sql                 # Sample student data
│   └── student_system.sqlite    # Auto-created SQLite database
├── includes/
│   ├── auth.php                 # Authentication guards & helpers
│   ├── footer.php               # Shared footer
│   ├── header.php               # HTML Head & CDN styles
│   └── navbar.php               # Responsive navigation bar
├── uploads/                     # Uploaded student photos
│   └── default_avatar.svg       # Default avatar image
├── dashboard.php                # Admin dashboard & analytics
├── export.php                   # CSV export endpoint
├── index.php                    # Entrypoint router
├── login.php                    # Login page with live clock
├── logout.php                   # Logout handler
├── README.md                    # Project documentation
├── register.php                 # Public student registration
├── reports.php                  # Reports, charts & print views
├── run.ps1                      # PowerShell launcher
├── serve.bat                    # Windows batch launcher
├── student_add.php              # Add student inside portal
├── student_delete.php           # Delete student record
├── student_edit.php             # Edit student record
├── student_view.php             # View student profile & ID card
└── students.php                 # Student list with search & pagination
```

---

## 🚀 How to Run the Project

### Option A: One-Click Quick Launch (Recommended)
1. Double-click `serve.bat` (or run `./run.ps1` in PowerShell).
2. The script will automatically launch the server and open your browser at:
   ```text
   http://localhost:8000
   ```

### Option B: Using XAMPP / Apache
1. Copy or move this folder into your XAMPP `htdocs` folder:
   ```text
   C:\xampp\htdocs\std_reg_sys
   ```
2. Start **Apache** and **MySQL** in XAMPP Control Panel.
3. Open `phpMyAdmin` (`http://localhost/phpmyadmin`) and import `database/schema.sql` and `database/seed.sql` (optional, the system also auto-creates tables on first run!).
4. Open your browser at:
   ```text
   http://localhost/std_reg_sys
   ```

---

## 🔐 Default Login Credentials

| Role | Username / Email | Password |
|---|---|---|
| **Administrator** | `admin` | `admin123` |
| **Student** | `rahul@example.com` | `student123` |

---

## 💻 Tech Stack
- **Frontend**: HTML5, CSS3, JavaScript (ES6+), Bootstrap 5.3, Bootstrap Icons, Chart.js
- **Backend**: PHP 7.4 / 8.0 / 8.1 / 8.2+
- **Database**: MySQL / MariaDB (with zero-configuration SQLite auto-fallback)

