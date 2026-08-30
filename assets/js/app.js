/**
 * App General UI Interactions & Chart Renderers
 */

function confirmDelete(id, name) {
    const modalEl = document.getElementById('deleteConfirmModal');
    if (modalEl) {
        document.getElementById('deleteStudentName').textContent = name;
        document.getElementById('deleteConfirmBtn').href = `student_delete.php?id=${id}`;
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    } else {
        if (confirm(`Are you sure you want to delete student: ${name}?`)) {
            window.location.href = `student_delete.php?id=${id}`;
        }
    }
}

