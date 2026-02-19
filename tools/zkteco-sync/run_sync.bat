@echo off
title ZKTeco Attendance Sync Tool
color 0B

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

:menu
cls
echo.
echo  ╔══════════════════════════════════════════════╗
echo  ║   ZKTeco K50 → XHRM Attendance Sync Tool    ║
echo  ╚══════════════════════════════════════════════╝
echo.
echo    1. Sync Now (Pull + Backup + Push via API)
echo    2. Sync Now (Pull + Backup + Push via CSV)
echo    3. Pull Only (Backup, no push)
echo    4. Export CSV (for manual import)
echo    5. Start Scheduled Sync
echo    6. Show Status
echo    7. Test Device Connection
echo    8. Test Server Connection
echo    9. Reset Sync Status (re-push all records)
echo   10. Push by Date Range (select dates to push)
echo   11. Run Diagnostics (saves to diagnose_output.txt)
echo   12. Exit
echo.
set /p choice="  Enter choice (1-12): "

if "%choice%"=="1" python sync.py
if "%choice%"=="2" python sync.py --push csv
if "%choice%"=="3" python sync.py --pull-only
if "%choice%"=="4" python sync.py --export-csv
if "%choice%"=="5" python sync.py --schedule
if "%choice%"=="6" python sync.py --status
if "%choice%"=="7" python sync.py --test-device
if "%choice%"=="8" python sync.py --test-server
if "%choice%"=="9" python sync.py --reset-sync
if "%choice%"=="10" python sync.py --push-date-range
if "%choice%"=="11" (
    python diagnose.py > diagnose_output.txt
    type diagnose_output.txt
    echo.
    echo  Output saved to: %cd%\diagnose_output.txt
)
if "%choice%"=="12" exit /b 0

echo.
echo  Press any key to return to menu...
pause >nul
goto menu
