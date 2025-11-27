@echo off
REM 🚀 Git Auto Push Script - Windows Batch Version
REM Usage: scripts\git-push.bat
REM Note: For best experience, use git-push.ps1 (PowerShell version)

setlocal enabledelayedexpansion

echo ================================
echo ^🚀 Git Auto Push (Windows Batch)
echo ================================
echo.

REM Get current branch
for /f "tokens=*" %%i in ('git branch --show-current') do set CURRENT_BRANCH=%%i
echo 📍 Current branch: %CURRENT_BRANCH%
echo.

REM Check for changes
git status --short > temp_status.txt
for %%A in (temp_status.txt) do set SIZE=%%~zA
del temp_status.txt

if %SIZE%==0 (
    echo ⚠️  No changes to commit!
    exit /b 0
)

REM Show changes
echo 📝 Changed files:
git status --short
echo.

REM Select commit type
echo Select commit type:
echo 1^) feat      - New feature
echo 2^) fix       - Bug fix
echo 3^) docs      - Documentation
echo 4^) style     - Code style
echo 5^) refactor  - Code refactoring
echo 6^) perf      - Performance
echo 7^) test      - Tests
echo 8^) build     - Build system
echo 9^) ci        - CI/CD
echo 10^) chore    - Maintenance
echo.

set /p TYPE_CHOICE="Enter choice [1-10]: "

if "%TYPE_CHOICE%"=="1" set TYPE=feat
if "%TYPE_CHOICE%"=="2" set TYPE=fix
if "%TYPE_CHOICE%"=="3" set TYPE=docs
if "%TYPE_CHOICE%"=="4" set TYPE=style
if "%TYPE_CHOICE%"=="5" set TYPE=refactor
if "%TYPE_CHOICE%"=="6" set TYPE=perf
if "%TYPE_CHOICE%"=="7" set TYPE=test
if "%TYPE_CHOICE%"=="8" set TYPE=build
if "%TYPE_CHOICE%"=="9" set TYPE=ci
if "%TYPE_CHOICE%"=="10" set TYPE=chore

if not defined TYPE (
    echo Invalid choice!
    exit /b 1
)

REM Get scope
echo.
set /p SCOPE="Scope (optional): "

REM Get message
echo.
set /p MESSAGE="Commit message: "

if "%MESSAGE%"=="" (
    echo ❌ Message cannot be empty!
    exit /b 1
)

REM Build commit message
if "%SCOPE%"=="" (
    set COMMIT_MSG=%TYPE%: %MESSAGE%
) else (
    set COMMIT_MSG=%TYPE%(%SCOPE%): %MESSAGE%
)

REM Confirm
echo.
echo Commit message: %COMMIT_MSG%
echo.
set /p CONFIRM="Continue? [Y/n]: "

if /i "%CONFIRM%"=="n" (
    echo Cancelled.
    exit /b 0
)

REM Execute git commands
echo.
echo 📦 Adding files...
git add .

echo 💾 Committing...
git commit -m "%COMMIT_MSG%"

echo 📤 Pushing to %CURRENT_BRANCH%...
git push origin %CURRENT_BRANCH%

echo.
echo ✅ Successfully pushed to %CURRENT_BRANCH%!
echo 📝 Commit: %COMMIT_MSG%

REM Ask about merge to develop
echo.
set /p MERGE_DEVELOP="Merge into develop? [Y/n]: "

if /i not "%MERGE_DEVELOP%"=="n" (
    echo 🔀 Merging into develop...
    
    git checkout develop
    git pull origin develop
    git merge %CURRENT_BRANCH% --no-edit
    
    if !errorlevel! equ 0 (
        git push origin develop
        echo ✅ Merged to develop!
        git checkout %CURRENT_BRANCH%
        echo.
        echo 🎉 All done!
        echo ✅ Pushed to: %CURRENT_BRANCH%
        echo ✅ Merged to: develop
    ) else (
        echo ❌ Merge conflict! Please resolve manually.
        exit /b 1
    )
) else (
    echo Skipped merge to develop
)

endlocal
