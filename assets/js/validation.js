/**
 * JavaScript Client-Side Form Validation
 * Satisfies Requirement Phase 1 (d)
 */

document.addEventListener('DOMContentLoaded', () => {
    const studentForm = document.getElementById('studentRegistrationForm');
    if (!studentForm) return;

    // Real-time photo preview
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photoPreview');

    if (photoInput && photoPreview) {
        photoInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                // Check file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    showValidationAlert('Photo size must not exceed 2MB!', 'warning');
                    photoInput.value = '';
                    return;
                }

                // Check file extension
                const allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
                if (!allowed.includes(file.type)) {
                    showValidationAlert('Only JPG, PNG, and WebP images are allowed!', 'warning');
                    photoInput.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = (event) => {
                    photoPreview.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Form submit validation
    studentForm.addEventListener('submit', (e) => {
        let isValid = true;
        let errorMessages = [];

        // 1. Full Name Validation
        const fullname = document.getElementById('fullname');
        if (fullname) {
            if (!fullname.value.trim()) {
                markInvalid(fullname, 'Full name is required.');
                errorMessages.push('Full Name is required.');
                isValid = false;
            } else if (fullname.value.trim().length < 3) {
                markInvalid(fullname, 'Full name must be at least 3 characters.');
                errorMessages.push('Full Name must be at least 3 characters.');
                isValid = false;
            } else {
                markValid(fullname);
            }
        }

        // 2. Email Validation
        const email = document.getElementById('email');
        if (email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email.value.trim()) {
                markInvalid(email, 'Email address is required.');
                errorMessages.push('Email address is required.');
                isValid = false;
            } else if (!emailRegex.test(email.value.trim())) {
                markInvalid(email, 'Please enter a valid email address (e.g. rahul@example.com).');
                errorMessages.push('Invalid email format.');
                isValid = false;
            } else {
                markValid(email);
            }
        }

        // 3. Mobile Number Validation (10 digits)
        const mobile = document.getElementById('mobile');
        if (mobile) {
            const mobileRegex = /^[0-9]{10}$/;
            const cleanMobile = mobile.value.replace(/[^0-9]/g, '');
            if (!mobile.value.trim()) {
                markInvalid(mobile, 'Mobile number is required.');
                errorMessages.push('Mobile number is required.');
                isValid = false;
            } else if (!mobileRegex.test(cleanMobile)) {
                markInvalid(mobile, 'Mobile number must be exactly 10 digits.');
                errorMessages.push('Mobile number must be exactly 10 digits.');
                isValid = false;
            } else {
                markValid(mobile);
            }
        }

        // 4. Date of Birth Validation
        const dob = document.getElementById('dob');
        if (dob) {
            if (!dob.value) {
                markInvalid(dob, 'Date of Birth is required.');
                errorMessages.push('Date of Birth is required.');
                isValid = false;
            } else {
                markValid(dob);
            }
        }

        // 5. Gender Radio Validation
        const genderRadios = document.querySelectorAll('input[name="gender"]');
        if (genderRadios.length > 0) {
            const genderSelected = Array.from(genderRadios).some(r => r.checked);
            const genderContainer = document.getElementById('genderContainer');
            if (!genderSelected) {
                if (genderContainer) genderContainer.classList.add('border', 'border-danger', 'p-2', 'rounded');
                errorMessages.push('Please select a gender.');
                isValid = false;
            } else {
                if (genderContainer) genderContainer.classList.remove('border', 'border-danger');
            }
        }

        // 6. Course & Semester Dropdown Validation
        const course = document.getElementById('course');
        if (course) {
            if (!course.value) {
                markInvalid(course, 'Please select a course.');
                errorMessages.push('Please select a course.');
                isValid = false;
            } else {
                markValid(course);
            }
        }

        const semester = document.getElementById('semester');
        if (semester) {
            if (!semester.value) {
                markInvalid(semester, 'Please select a semester.');
                errorMessages.push('Please select a semester.');
                isValid = false;
            } else {
                markValid(semester);
            }
        }

        // 7. Address Validation
        const address = document.getElementById('address');
        if (address) {
            if (!address.value.trim()) {
                markInvalid(address, 'Address is required.');
                errorMessages.push('Address is required.');
                isValid = false;
            } else {
                markValid(address);
            }
        }

        // 8. Password Match Validation (if password fields are present)
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const isEditMode = studentForm.dataset.mode === 'edit';

        if (password && (!isEditMode || password.value.length > 0)) {
            if (!password.value) {
                markInvalid(password, 'Password is required.');
                errorMessages.push('Password is required.');
                isValid = false;
            } else if (password.value.length < 6) {
                markInvalid(password, 'Password must be at least 6 characters.');
                errorMessages.push('Password must be at least 6 characters.');
                isValid = false;
            } else {
                markValid(password);
            }

            if (confirmPassword) {
                if (confirmPassword.value !== password.value) {
                    markInvalid(confirmPassword, 'Passwords do not match.');
                    errorMessages.push('Passwords do not match.');
                    isValid = false;
                } else {
                    markValid(confirmPassword);
                }
            }
        }

        if (!isValid) {
            e.preventDefault();
            // Show alert box
            showValidationAlert('<strong>Please fix the errors below:</strong><br>' + errorMessages.slice(0, 3).join('<br>'), 'danger');
        }
    });

    // Reset button functionality
    const resetBtn = document.getElementById('resetBtn');
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            const inputs = studentForm.querySelectorAll('.is-invalid, .is-valid');
            inputs.forEach(el => el.classList.remove('is-invalid', 'is-valid'));
            const alertBox = document.getElementById('formAlertBox');
            if (alertBox) alertBox.classList.add('d-none');
            if (photoPreview) {
                photoPreview.src = 'uploads/default_avatar.svg';
            }
        });
    }

    function markInvalid(element, msg) {
        element.classList.add('is-invalid');
        element.classList.remove('is-valid');
        let feedback = element.nextElementSibling;
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            const parent = element.parentElement;
            feedback = parent.querySelector('.invalid-feedback');
        }
        if (feedback) feedback.textContent = msg;
    }

    function markValid(element) {
        element.classList.remove('is-invalid');
        element.classList.add('is-valid');
    }

    function showValidationAlert(message, type = 'danger') {
        let alertBox = document.getElementById('formAlertBox');
        if (!alertBox) {
            alertBox = document.createElement('div');
            alertBox.id = 'formAlertBox';
            studentForm.prepend(alertBox);
        }
        alertBox.className = `alert alert-${type} alert-dismissible fade show shadow-sm mb-4`;
        alertBox.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <div>${message}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});

