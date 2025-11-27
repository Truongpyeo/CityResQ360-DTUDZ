# 🚀 Git Auto Push Script - Windows PowerShell Version
# Usage: .\scripts\git-push.ps1
# Supports: Scopes, Breaking Changes, Multi-line messages, Auto-merge to develop

# Set strict mode
$ErrorActionPreference = "Stop"

# Colors
function Write-Color {
    param(
        [string]$Text,
        [string]$Color = "White"
    )
    Write-Host $Text -ForegroundColor $Color
}

# Print header
Write-Color "================================" "Blue"
Write-Color "🚀 Git Auto Push v2.0 (Windows)" "Blue"
Write-Color "================================" "Blue"
Write-Host ""

# Get current branch
$currentBranch = git branch --show-current
Write-Color "📍 Current branch: " "Green" -NoNewline
Write-Color $currentBranch "Yellow"
Write-Host ""

# Check for uncommitted changes
$status = git status --short
if ([string]::IsNullOrWhiteSpace($status)) {
    Write-Color "⚠️  No changes to commit!" "Red"
    exit 0
}

# Show changed files
Write-Color "📝 Changed files:" "Blue"
git status --short
Write-Host ""

# Select commit type
Write-Color "Select commit type:" "Blue"
Write-Host "1) ✨ feat      - New feature"
Write-Host "2) 🐛 fix       - Bug fix"
Write-Host "3) 📚 docs      - Documentation"
Write-Host "4) 💄 style     - Code style (formatting)"
Write-Host "5) ♻️  refactor - Code refactoring"
Write-Host "6) ⚡ perf      - Performance improvement"
Write-Host "7) ✅ test      - Add/update tests"
Write-Host "8) 🔨 build     - Build system changes"
Write-Host "9) 👷 ci        - CI/CD changes"
Write-Host "10) 🔧 chore    - Maintenance tasks"
Write-Host ""
$typeChoice = Read-Host "Enter choice [1-10]"

$type = switch ($typeChoice) {
    "1" { "feat" }
    "2" { "fix" }
    "3" { "docs" }
    "4" { "style" }
    "5" { "refactor" }
    "6" { "perf" }
    "7" { "test" }
    "8" { "build" }
    "9" { "ci" }
    "10" { "chore" }
    default {
        Write-Color "Invalid choice!" "Red"
        exit 1
    }
}

# Optional scope
Write-Host ""
$scope = Read-Host "Scope (optional, e.g., auth, api, docker)"

# Commit message (header)
Write-Host ""
Write-Color "📝 Commit header (short description):" "Cyan"
$message = Read-Host ">"

if ([string]::IsNullOrWhiteSpace($message)) {
    Write-Color "❌ Message cannot be empty!" "Red"
    exit 1
}

# Optional body (multi-line)
Write-Host ""
Write-Color "📄 Commit body (optional, press Enter on empty line to finish):" "Cyan"
$body = ""
while ($true) {
    $line = Read-Host
    if ([string]::IsNullOrWhiteSpace($line)) {
        break
    }
    $body += "$line`n"
}

# Breaking change check
Write-Host ""
$isBreaking = Read-Host "Is this a BREAKING CHANGE? [y/N]"

$breakingChange = ""
$commitHeader = ""

if ($isBreaking -match "^[Yy]$") {
    Write-Color "⚠️  Describe the breaking change:" "Yellow"
    $breakingDesc = Read-Host ">"
    
    if (-not [string]::IsNullOrWhiteSpace($breakingDesc)) {
        $breakingChange = "`n`nBREAKING CHANGE: $breakingDesc"
        
        # Add ! to header for breaking changes
        if ([string]::IsNullOrWhiteSpace($scope)) {
            $commitHeader = "${type}!: $message"
        } else {
            $commitHeader = "${type}(${scope})!: $message"
        }
    }
} else {
    # Build normal commit header
    if ([string]::IsNullOrWhiteSpace($scope)) {
        $commitHeader = "${type}: $message"
    } else {
        $commitHeader = "${type}(${scope}): $message"
    }
}

# Build full commit message
$commitMsg = $commitHeader
if (-not [string]::IsNullOrWhiteSpace($body)) {
    $commitMsg += "`n`n$body"
}
if (-not [string]::IsNullOrWhiteSpace($breakingChange)) {
    $commitMsg += $breakingChange
}

# Show preview
Write-Host ""
Write-Color "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" "Yellow"
Write-Color "📋 Commit message preview:" "Yellow"
Write-Color "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" "Yellow"
Write-Color $commitMsg "Green"
Write-Color "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" "Yellow"
Write-Host ""

# Confirm
$confirm = Read-Host "Continue? [Y/n]"

if ($confirm -match "^[Nn]$") {
    Write-Color "Cancelled." "Red"
    exit 0
}

# Execute git commands
Write-Host ""
Write-Color "📦 Adding files..." "Blue"
git add .

Write-Color "💾 Committing..." "Blue"
# Write commit message to temp file and use it
$tempFile = [System.IO.Path]::GetTempFileName()
$commitMsg | Out-File -FilePath $tempFile -Encoding UTF8
git commit -F $tempFile
Remove-Item $tempFile

Write-Color "📤 Pushing to $currentBranch..." "Blue"
git push origin $currentBranch

Write-Host ""
Write-Color "✅ Successfully pushed to $currentBranch!" "Green"
Write-Color "📝 Commit header: $commitHeader" "Green"

# Show if breaking change
if (-not [string]::IsNullOrWhiteSpace($breakingChange)) {
    Write-Color "⚠️  BREAKING CHANGE committed!" "Red"
}

# Auto-merge to develop
Write-Host ""
Write-Color "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" "Cyan"
$mergeDevelop = Read-Host "Merge into develop branch? [Y/n]"

if ($mergeDevelop -notmatch "^[Nn]$") {
    # Check if develop branch exists
    $developExists = git show-ref --verify --quiet refs/heads/develop
    
    if ($LASTEXITCODE -ne 0) {
        Write-Color "⚠️  develop branch doesn't exist locally" "Yellow"
        $createDevelop = Read-Host "Create develop branch from main? [Y/n]"
        
        if ($createDevelop -notmatch "^[Nn]$") {
            git checkout -b develop main
            git push -u origin develop
            Write-Color "✅ Created develop branch" "Green"
        } else {
            Write-Color "Skipping merge to develop" "Yellow"
            exit 0
        }
    }
    
    Write-Host ""
    Write-Color "🔀 Merging into develop..." "Blue"
    
    # Save current branch
    $sourceBranch = $currentBranch
    
    # Checkout develop
    git checkout develop
    
    # Pull latest develop
    Write-Color "📥 Pulling latest develop..." "Blue"
    git pull origin develop
    
    # Merge current branch into develop
    Write-Color "🔀 Merging $sourceBranch into develop..." "Blue"
    
    $mergeResult = git merge $sourceBranch --no-edit 2>&1
    
    if ($LASTEXITCODE -eq 0) {
        Write-Color "✅ Merge successful!" "Green"
        
        # Push develop
        Write-Color "📤 Pushing develop..." "Blue"
        git push origin develop
        
        Write-Color "✅ develop branch updated!" "Green"
    } else {
        Write-Color "❌ Merge conflict detected!" "Red"
        Write-Color "Please resolve conflicts manually:" "Yellow"
        Write-Color "  1. Fix conflicts in the files" "Cyan"
        Write-Color "  2. git add <resolved-files>" "Cyan"
        Write-Color "  3. git commit" "Cyan"
        Write-Color "  4. git push origin develop" "Cyan"
        Write-Color "  5. git checkout $sourceBranch" "Cyan"
        exit 1
    }
    
    # Return to original branch
    Write-Host ""
    Write-Color "🔙 Returning to $sourceBranch..." "Blue"
    git checkout $sourceBranch
    
    Write-Host ""
    Write-Color "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" "Green"
    Write-Color "🎉 All done!" "Green"
    Write-Color "✅ Pushed to: $sourceBranch" "Green"
    Write-Color "✅ Merged to: develop" "Green"
    Write-Color "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" "Green"
} else {
    Write-Color "Skipped merge to develop" "Yellow"
}
