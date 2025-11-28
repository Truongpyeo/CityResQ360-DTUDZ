@echo off
REM 🚀 Git Auto Push Script - Windows Batch Version
REM Usage: scripts\git-push.bat
REM Note: For best multi-line support, use git-push.ps1 (PowerShell version)

setlocal enabledelayedexpansion

echo ================================
echo ^🚀 Git Auto Push v2.0 (Windows)
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
echo 1^) ✨ feat      - New feature
echo 2^) 🐛 fix       - Bug fix
echo 3^) 📚 docs      - Documentation
echo 4^) 💄 style     - Code style
echo 5^) ♻️  refactor - Code refactoring
echo 6^) ⚡ perf      - Performance
echo 7^) ✅ test      - Tests
echo 8^) 🔨 build     - Build system
echo 9^) 👷 ci        - CI/CD
echo 10^) 🔧 chore    - Maintenance
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
    echo ❌ Invalid choice!
    exit /b 1
)

REM Get scope
echo.
set /p SCOPE="Scope (optional, e.g., auth, api, docker): "

REM Get message header
echo.
echo 📝 Commit header (short description):
set /p MESSAGE="> "

if "%MESSAGE%"=="" (
    echo ❌ Message cannot be empty!
    exit /b 1
)

REM Get body (multi-line)
echo.
echo 📄 Commit body (optional, multi-line. Enter '.' on new line to finish):
set BODY=
set "BODY_FILE=%TEMP%\git_body_%RANDOM%.txt"
:BODY_LOOP
set /p "BODY_LINE=> "
if "%BODY_LINE%"=="." goto BODY_DONE
echo %BODY_LINE%>> "%BODY_FILE%"
goto BODY_LOOP
:BODY_DONE

REM Breaking change
echo.
set /p IS_BREAKING="Is this a BREAKING CHANGE? [y/N]: "

set BREAKING_CHANGE=
if /i "%IS_BREAKING%"=="y" (
    echo ⚠️  Describe the breaking change (enter '.' to finish):
    set "BREAKING_FILE=%TEMP%\git_breaking_%RANDOM%.txt"
    :BREAKING_LOOP
    set /p "BREAKING_LINE=> "
    if "%BREAKING_LINE%"=="." goto BREAKING_DONE
    echo %BREAKING_LINE%>> "%BREAKING_FILE%"
    goto BREAKING_LOOP
    :BREAKING_DONE
)

REM Build commit header
if "%SCOPE%"=="" (
    if /i "%IS_BREAKING%"=="y" (
        set COMMIT_HEADER=%TYPE%!: %MESSAGE%
    ) else (
        set COMMIT_HEADER=%TYPE%: %MESSAGE%
    )
) else (
    if /i "%IS_BREAKING%"=="y" (
        set COMMIT_HEADER=%TYPE%(%SCOPE%)!: %MESSAGE%
    ) else (
        set COMMIT_HEADER=%TYPE%(%SCOPE%): %MESSAGE%
    )
)

REM Build full commit message in temp file
set "COMMIT_FILE=%TEMP%\git_commit_%RANDOM%.txt"
echo %COMMIT_HEADER%> "%COMMIT_FILE%"

if exist "%BODY_FILE%" (
    echo.>> "%COMMIT_FILE%"
    type "%BODY_FILE%" >> "%COMMIT_FILE%"
    del "%BODY_FILE%"
)

if exist "%BREAKING_FILE%" (
    echo.>> "%COMMIT_FILE%"
    echo BREAKING CHANGE:>> "%COMMIT_FILE%"
    type "%BREAKING_FILE%" >> "%COMMIT_FILE%"
    del "%BREAKING_FILE%"
)

REM Show preview
echo.
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo 📋 Commit message preview:
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
type "%COMMIT_FILE%"
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.

REM Confirm
set /p CONFIRM="Continue? [Y/n]: "

if /i "%CONFIRM%"=="n" (
    del "%COMMIT_FILE%"
    echo Cancelled.
    exit /b 0
)

REM Execute git commands
echo.
echo 📦 Adding files...
git add .

echo 💾 Committing...
git commit -F "%COMMIT_FILE%"
del "%COMMIT_FILE%"

echo 📤 Pushing to %CURRENT_BRANCH%...
git push origin %CURRENT_BRANCH%

echo.
echo ✅ Successfully pushed to %CURRENT_BRANCH%!
echo 📝 Commit: %COMMIT_HEADER%

if /i "%IS_BREAKING%"=="y" (
    echo ⚠️  BREAKING CHANGE committed!
)

REM Ask about merge to develop
echo.
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
set /p MERGE_DEVELOP="Merge into develop? [Y/n]: "

if /i not "%MERGE_DEVELOP%"=="n" (
    echo 🔀 Merging into develop...
    
    git checkout develop
    echo 📥 Pulling latest develop...
    git pull origin develop
    
    echo 🔀 Merging %CURRENT_BRANCH% into develop...
    git merge %CURRENT_BRANCH% --no-edit
    
    if !errorlevel! equ 0 (
        echo ✅ Merge successful!
        echo 📤 Pushing develop...
        git push origin develop
        echo ✅ develop branch updated!
        
        echo.
        echo 🔙 Returning to %CURRENT_BRANCH%...
        git checkout %CURRENT_BRANCH%
        
        echo.
        echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        echo 🎉 All done!
        echo ✅ Pushed to: %CURRENT_BRANCH%
        echo ✅ Merged to: develop
        echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    ) else (
        echo ❌ Merge conflict! Please resolve manually.
        exit /b 1
    )
) else (
    echo Skipped merge to develop
)

endlocal
