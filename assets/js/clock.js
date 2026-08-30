/**
 * Live Date & Time Clock for Login Page
 * Satisfies Requirement Phase 1 (a)
 */

function updateLiveClock() {
    const clockElement = document.getElementById('liveClockDisplay');
    const dateElement = document.getElementById('liveDateDisplay');
    const timeElement = document.getElementById('liveTimeDisplay');

    if (!clockElement && !dateElement && !timeElement) return;

    const now = new Date();

    // Options for formatted date (e.g. 22 May 2025)
    const dateOptions = { day: '2-digit', month: 'short', year: 'numeric' };
    const formattedDate = now.toLocaleDateString('en-GB', dateOptions);

    // Options for formatted time (e.g. 10:45:30 AM)
    const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
    const formattedTime = now.toLocaleTimeString('en-US', timeOptions);

    if (dateElement) dateElement.textContent = formattedDate;
    if (timeElement) timeElement.textContent = formattedTime;
    if (clockElement) {
        clockElement.innerHTML = `<span><i class="bi bi-calendar-event me-1"></i>Date : <strong>${formattedDate}</strong></span> <span class="mx-2 text-muted">|</span> <span><i class="bi bi-clock me-1"></i>Time : <strong>${formattedTime}</strong></span>`;
    }
}

// Start immediately and repeat every second
document.addEventListener('DOMContentLoaded', () => {
    updateLiveClock();
    setInterval(updateLiveClock, 1000);
});

