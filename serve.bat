@echo off
title Student Registration System - Local Server
echo =====================================================================
echo           STUDENT REGISTRATION SYSTEM (PHP & MySQL)
echo =====================================================================
echo.
echo Starting local PHP development server...
echo.

if exist "C:\xampp\php\php.exe" (
    echo Using XAMPP PHP: C:\xampp\php\php.exe
    set "PHP_CMD=C:\xampp\php\php.exe"
) else (
    set "PHP_CMD=php"
)

echo Access URL: http://localhost:8000
echo.
echo Press Ctrl+C anytime to stop the server.
echo =====================================================================
start http://localhost:8000
"%PHP_CMD%" -S 127.0.0.1:8000
pause

