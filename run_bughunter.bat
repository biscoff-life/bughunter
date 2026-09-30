@echo off
echo ========================================================
echo   BugHunter - Interactive Debugging Platform
echo   "Find the bug. Fix the code. Understand why. Level up."
echo ========================================================
echo.
echo Starting BugHunter local server on http://localhost:8000 ...
echo.

cd /d "%~dp0"
start "" http://localhost:8000
php -c php.ini -S localhost:8000
pause
