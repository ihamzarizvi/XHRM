@echo off
title ZKTeco Attendance Sync Tool
color 0B
echo.
echo  ╔══════════════════════════════════════════════╗
echo  ║   ZKTeco K50 → XHRM Attendance Sync Tool    ║
echo  ╚══════════════════════════════════════════════╝
echo.

cd /d "%~dp0"

:: Check for Python
python --version >nul 2>&1
if errorlevel 1 (
    echo  [ERROR] Python is not installed or not in PATH.
    echo  Download from: https://www.python.org/downloads/
    echo.
    pause
    exit /b 1
)

:: Install dependencies if needed
if not exist ".venv" (
    echo  Setting up virtual environment...
    python -m venv .venv
    call .venv\Scripts\activate.bat
    pip install -r requirements.txt
    echo.
    echo  Setup complete!
    echo.
) else (
    call .venv\Scripts\activate.bat
)

echo  Select an option:
echo.
echo    1. Sync Now (Pull + Backup + Push via API)
echo    2. Sync Now (Pull + Backup + Push via CSV)
echo    3. Pull Only (Backup, no push)
echo    4. Export CSV (for manual import)
echo    5. Start Scheduled Sync
echo    6. Show Status
echo    7. Test Device Connection
echo    8. Test Server Connection
echo    9. Exit
echo.
set /p choice="  Enter choice (1-9): "

if "%choice%"=="1" python sync.py
if "%choice%"=="2" python sync.py --push csv
if "%choice%"=="3" python sync.py --pull-only
if "%choice%"=="4" python sync.py --export-csv
if "%choice%"=="5" python sync.py --schedule
if "%choice%"=="6" python sync.py --status
if "%choice%"=="7" python sync.py --test-device
if "%choice%"=="8" python sync.py --test-server
if "%choice%"=="9" exit /b 0

echo.
pause
