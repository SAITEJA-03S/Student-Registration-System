# Student Registration System Launcher
Write-Host "=====================================================================" -ForegroundColor Cyan
Write-Host "          STUDENT REGISTRATION SYSTEM (PHP & MySQL)" -ForegroundColor Yellow
Write-Host "=====================================================================" -ForegroundColor Cyan

$phpPath = "php"
if (Test-Path "C:\xampp\php\php.exe") {
    $phpPath = "C:\xampp\php\php.exe"
}

Write-Host "Starting server on http://localhost:8000 using $phpPath..." -ForegroundColor Green
Start-Process "http://localhost:8000"
& $phpPath -S 127.0.0.1:8000

