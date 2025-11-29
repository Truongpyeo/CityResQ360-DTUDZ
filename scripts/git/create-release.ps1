# Create Release Script with Auto-Generated Release Notes
# Usage: .\scripts\create-release.ps1

$ErrorActionPreference = "Stop"

function Write-Color {
    param([string]$Text, [string]$Color = "White")
    Write-Host $Text -ForegroundColor $Color
}

Write-Color "====================================" "Blue"
Write-Color " Create Release v2.0 (Auto-Gen)" "Blue"
Write-Color "====================================" "Blue"
Write-Host ""

$originalBranch = git branch --show-current
Write-Color "[*] Current branch: $originalBranch" "Cyan"

try {
    # Checkout and update branches
    Write-Host ""
    Write-Color "[*] Updating develop..." "Blue"
    git checkout develop
    git pull origin develop
    
    Write-Host ""
    Write-Color "[*] Updating master..." "Blue"
    git checkout master
    git pull origin master
    
    # Merge develop into master
    Write-Host ""
    Write-Color "[*] Merging develop into master..." "Yellow"
    git merge develop --no-ff -m "chore: merge develop into master for release"
    
    if ($LASTEXITCODE -ne 0) {
        Write-Color "[X] Merge failed! Resolve conflicts and try again." "Red"
        exit 1
    }
    
    Write-Color "[OK] Merge successful!" "Green"
    git push origin master
    
    # Detect latest tag
    Write-Host ""
    Write-Color "[*] Detecting latest tag..." "Cyan"
    
    $latestTag = git describe --tags --abbrev=0 2>$null
    
    if ([string]::IsNullOrWhiteSpace($latestTag)) {
        $latestTag = "v0.0.0"
        Write-Color "[!] No tags found, starting from v0.0.0" "Yellow"
    } else {
        Write-Color "[*] Latest tag: $latestTag" "Green"
    }
    
    # Parse version
    $versionNum = $latestTag -replace '^v', ''
    $parts = $versionNum -split '\.'
    $major = [int]$parts[0]
    $minor = [int]$parts[1]
    $patch = [int]$parts[2]
    
    # Select version bump
    Write-Host ""
    Write-Color "Current version: $latestTag" "Yellow"
    Write-Host ""
    Write-Host "Select version bump:"
    Write-Host "1) PATCH   ($latestTag -> v$major.$minor.$($patch+1)) - Bug fixes"
    Write-Host "2) MINOR   ($latestTag -> v$major.$($minor+1).0) - New features"
    Write-Host "3) MAJOR   ($latestTag -> v$($major+1).0.0) - Breaking changes"
    Write-Host "4) Custom version"
    Write-Host "5) Cancel"
    Write-Host ""
    
    $choice = Read-Host "Select [1-5]"
    
    $newVersion = switch ($choice) {
        "1" { "v$major.$minor.$($patch+1)" }
        "2" { "v$major.$($minor+1).0" }
        "3" { "v$($major+1).0.0" }
        "4" {
            $custom = Read-Host "Enter version (e.g., 1.5.0)"
            "v$custom"
        }
        default {
            Write-Color "[!] Cancelled" "Yellow"
            git checkout $originalBranch
            exit 0
        }
    }
    
    Write-Host ""
    Write-Color "[*] New version: $newVersion" "Green"
    
    # Auto-generate release notes from commits
    Write-Host ""
    Write-Color "[*] Auto-generating release notes from commits..." "Cyan"
    
    $commits = @()
    if ($latestTag -ne "v0.0.0") {
        $commits = git log "$latestTag..HEAD" --pretty=format:"%s|||%an" --no-merges
    } else {
        $commits = git log --pretty=format:"%s|||%an" --no-merges -20
    }
    
    # Categorize commits
    $features = @()
    $fixes = @()
    $docs = @()
    $others = @()
    $breaking = @()
    
    foreach ($commit in $commits) {
        if ([string]::IsNullOrWhiteSpace($commit)) { continue }
        
        $parts = $commit -split '\|\|\|'
        $msg = $parts[0]
        $author = if ($parts.Length -gt 1) { $parts[1] } else { "Unknown" }
        
        if ($msg -match '^feat[\(\!]|^feat:') {
            $cleanMsg = $msg -replace '^feat[\(\!:].*?[:\)]?\s*', ''
            $features += "- $cleanMsg"
        }
        elseif ($msg -match '^fix[\(\!]|^fix:') {
            $cleanMsg = $msg -replace '^fix[\(\!:].*?[:\)]?\s*', ''
            $fixes += "- $cleanMsg"
        }
        elseif ($msg -match '^docs[\(\!]|^docs:') {
            $cleanMsg = $msg -replace '^docs[\(\!:].*?[:\)]?\s*', ''
            $docs += "- $cleanMsg"
        }
        else {
            $cleanMsg = $msg -replace '^[a-z]+[\(\!:].*?[:\)]?\s*', ''
            $others += "- $cleanMsg"
        }
        
        if ($msg -match '!' -or $msg -match 'BREAKING CHANGE') {
            $breaking += "- $msg"
        }
    }
    
    # Get release title
    Write-Host ""
    Write-Color "[*] Enter release title (or press Enter for auto-title):" "Cyan"
    $releaseTitle = Read-Host ">"
    
    if ([string]::IsNullOrWhiteSpace($releaseTitle)) {
        $releaseTitle = "Release $newVersion"
    }
    
    # Generate timestamp
    $timestamp = Get-Date -Format "dd/MM/yyyy - HH'h'mm"
    
    # Build CHANGELOG entry
    $changelogEntry = "## $timestamp`n`n### $releaseTitle`n`n"
    
    if ($features.Count -gt 0) {
        $changelogEntry += "**New Features:**`n"
        $changelogEntry += ($features -join "`n") + "`n`n"
    }
    
    if ($fixes.Count -gt 0) {
        $changelogEntry += "**Bug Fixes:**`n"
        $changelogEntry += ($fixes -join "`n") + "`n`n"
    }
    
    if ($docs.Count -gt 0) {
        $changelogEntry += "**Documentation:**`n"
        $changelogEntry += ($docs -join "`n") + "`n`n"
    }
    
    if ($others.Count -gt 0) {
        $changelogEntry += "**Other Changes:**`n"
        $changelogEntry += ($others -join "`n") + "`n`n"
    }
    
    if ($breaking.Count -gt 0) {
        $changelogEntry += "**BREAKING CHANGES:**`n"
        $changelogEntry += ($breaking -join "`n") + "`n`n"
    }
    
    $changelogEntry += "**Technical Details:**`n"
    $changelogEntry += "- Tag: $newVersion`n"
    $changelogEntry += "- Commits: $($commits.Count)`n"
    $changelogEntry += "- Released from: master branch`n"
    $changelogEntry += "- Release URL: https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/releases/tag/$newVersion`n`n"
    $changelogEntry += "---`n`n"
    
    # Show preview
    Write-Host ""
    Write-Color "====================================" "Yellow"
    Write-Color " CHANGELOG Preview" "Yellow"
    Write-Color "====================================" "Yellow"
    Write-Host $changelogEntry
    Write-Color "====================================" "Yellow"
    Write-Host ""
    
    $confirm = Read-Host "Create release with auto-generated notes? [Y/n]"
    
    if ($confirm -match "^[Nn]$") {
        Write-Color "[!] Cancelled" "Yellow"
        git checkout $originalBranch
        exit 0
    }
    
    # Update CHANGELOG.md
    Write-Host ""
    Write-Color "[*] Updating CHANGELOG.md..." "Blue"
    
    $changelogPath = "CHANGELOG.md"
    
    if (Test-Path $changelogPath) {
        $existing = Get-Content $changelogPath -Raw
        $newContent = "# CHANGELOG`n`n$changelogEntry$existing"
    } else {
        $newContent = "# CHANGELOG`n`nAll notable changes to CityResQ360-DTUDZ will be documented in this file.`n`n$changelogEntry"
    }
    
    $newContent | Out-File -FilePath $changelogPath -Encoding UTF8 -NoNewline
    
    Write-Color "[OK] CHANGELOG.md updated!" "Green"
    
    # Commit CHANGELOG
    git add CHANGELOG.md
    git commit -m "docs: update CHANGELOG for $newVersion"
    
    # Create tag
    Write-Host ""
    Write-Color "[*] Creating tag $newVersion..." "Blue"
    
    $tagMsg = "$releaseTitle`n`nAuto-generated from commits.`nReleased: $timestamp"
    git tag -a $newVersion -m $tagMsg
    
    Write-Color "[OK] Tag created!" "Green"
    
    # Push
    Write-Host ""
    Write-Color "[*] Pushing changes..." "Blue"
    git push origin master
    git push origin $newVersion
    
    # Success
    Write-Host ""
    Write-Color "====================================" "Green"
    Write-Color " Release $newVersion Created!" "Green"
    Write-Color "====================================" "Green"
    Write-Host ""
    Write-Color "[OK] Tag: $newVersion" "Green"
    Write-Color "[OK] Released at: $timestamp" "Green"
    Write-Color "[OK] Commits included: $($commits.Count)" "Green"
    Write-Host ""
    Write-Color "[*] GitHub Actions will create release page" "Cyan"
    Write-Host ""
    Write-Host "View at: https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/releases/tag/$newVersion" -ForegroundColor Cyan
    
} catch {
    Write-Color "[X] Error: $_" "Red"
    exit 1
} finally {
    Write-Host ""
    Write-Color "[*] Returning to $originalBranch..." "Blue"
    git checkout $originalBranch
}

Write-Host ""
Write-Color "[OK] Done!" "Green"
