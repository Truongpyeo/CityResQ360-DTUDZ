# Create Release Script - Auto merge develop to master and create release
# Usage: .\scripts\create-release.ps1

$ErrorActionPreference = "Stop"

# Colors
function Write-Color {
    param([string]$Text, [string]$Color = "White")
    Write-Host $Text -ForegroundColor $Color
}

Write-Color "====================================" "Blue"
Write-Color " Create Release v1.0" "Blue"
Write-Color "====================================" "Blue"
Write-Host ""

# Save current branch
$originalBranch = git branch --show-current
Write-Color "[*] Current branch: $originalBranch" "Cyan"

try {
    # Step 1: Checkout and update develop
    Write-Host ""
    Write-Color "[*] Checking out develop branch..." "Blue"
    git checkout develop
    
    Write-Color "[*] Pulling latest develop..." "Blue"
    git pull origin develop
    
    # Step 2: Checkout and update master
    Write-Host ""
    Write-Color "[*] Checking out master branch..." "Blue"
    git checkout master
    
    Write-Color "[*] Pulling latest master..." "Blue"
    git pull origin master
    
    # Step 3: Merge develop into master
    Write-Host ""
    Write-Color "[*] Merging develop into master..." "Yellow"
    git merge develop --no-ff -m "chore: merge develop into master for release"
    
    if ($LASTEXITCODE -ne 0) {
        Write-Color "[X] Merge failed! Please resolve conflicts manually." "Red"
        exit 1
    }
    
    Write-Color "[OK] Merge successful!" "Green"
    
    # Step 4: Push master
    Write-Color "[*] Pushing master..." "Blue"
    git push origin master
    
    # Step 5: Detect latest tag
    Write-Host ""
    Write-Color "[*] Detecting latest tag..." "Cyan"
    
    $latestTag = git describe --tags --abbrev=0 2>$null
    
    if ([string]::IsNullOrWhiteSpace($latestTag)) {
        $latestTag = "v0.0.0"
        Write-Color "[!] No existing tags found, starting from v0.0.0" "Yellow"
    } else {
        Write-Color "[*] Latest tag: $latestTag" "Green"
    }
    
    # Parse version
    $versionNum = $latestTag -replace '^v', ''
    $parts = $versionNum -split '\.'
    $major = [int]$parts[0]
    $minor = [int]$parts[1]
    $patch = [int]$parts[2]
    
    # Step 6: Ask for version bump
    Write-Host ""
    Write-Color "Current version: $latestTag" "Yellow"
    Write-Host ""
    Write-Host "Select version bump:"
    Write-Host "1) PATCH   ($latestTag → v$major.$minor.$($patch+1)) - Bug fixes"
    Write-Host "2) MINOR   ($latestTag → v$major.$($minor+1).0) - New features"
    Write-Host "3) MAJOR   ($latestTag → v$($major+1).0.0) - Breaking changes"
    Write-Host "4) Custom  - Enter version manually"
    Write-Host "5) Cancel"
    Write-Host ""
    
    $choice = Read-Host "Select [1-5]"
    
    $newVersion = ""
    switch ($choice) {
        "1" { $newVersion = "v$major.$minor.$($patch+1)" }
        "2" { $newVersion = "v$major.$($minor+1).0" }
        "3" { $newVersion = "v$($major+1).0.0" }
        "4" {
            $customVer = Read-Host "Enter version (e.g., 1.5.0)"
            $newVersion = "v$customVer"
        }
        default {
            Write-Color "[!] Cancelled" "Yellow"
            git checkout $originalBranch
            exit 0
        }
    }
    
    Write-Host ""
    Write-Color "[*] New version: $newVersion" "Green"
    
    # Step 7: Get release notes
    Write-Host ""
    Write-Color "[*] Enter release title (one line):" "Cyan"
    $releaseTitle = Read-Host ">"
    
    if ([string]::IsNullOrWhiteSpace($releaseTitle)) {
        $releaseTitle = "Release $newVersion"
    }
    
    Write-Host ""
    Write-Color "[*] Enter release notes (press Enter twice to finish):" "Cyan"
    $releaseNotes = @()
    $emptyCount = 0
    
    while ($true) {
        $line = Read-Host
        if ([string]::IsNullOrWhiteSpace($line)) {
            $emptyCount++
            if ($emptyCount -ge 2) { break }
        } else {
            $emptyCount = 0
            $releaseNotes += $line
        }
    }
    
    # Step 8: Generate timestamp
    $timestamp = Get-Date -Format "dd/MM/yyyy - HH'h'mm"
    
    # Step 9: Update CHANGELOG.md
    Write-Host ""
    Write-Color "[*] Updating CHANGELOG.md..." "Blue"
    
    $changelogPath = "CHANGELOG.md"
    $changelogExists = Test-Path $changelogPath
    
    # Build new entry
    $newEntry = @"
## $timestamp

### $releaseTitle

"@
    
    if ($releaseNotes.Count -gt 0) {
        $newEntry += "`n"
        foreach ($note in $releaseNotes) {
            $newEntry += "- $note`n"
        }
    }
    
    $newEntry += @"

**Technical Details:**
- Tag: $newVersion
- Released from: master branch
- Release URL: https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/releases/tag/$newVersion

---

"@
    
    # Read existing CHANGELOG or create new
    if ($changelogExists) {
        $existingContent = Get-Content $changelogPath -Raw
        $newContent = "# CHANGELOG`n`n$newEntry`n$existingContent"
    } else {
        $newContent = @"
# CHANGELOG

All notable changes to CityResQ360-DTUDZ will be documented in this file.

$newEntry
"@
    }
    
    # Write updated CHANGELOG
    $newContent | Out-File -FilePath $changelogPath -Encoding UTF8 -NoNewline
    
    Write-Color "[OK] CHANGELOG.md updated!" "Green"
    
    # Step 10: Commit CHANGELOG
    Write-Color "[*] Committing CHANGELOG..." "Blue"
    git add CHANGELOG.md
    git commit -m "docs: update CHANGELOG for $newVersion"
    
    # Step 11: Create tag
    Write-Host ""
    Write-Color "[*] Creating tag $newVersion..." "Blue"
    
    $tagMessage = "$releaseTitle`n`n"
    if ($releaseNotes.Count -gt 0) {
        $tagMessage += ($releaseNotes -join "`n") + "`n`n"
    }
    $tagMessage += "Released: $timestamp`nFrom: master branch"
    
    git tag -a $newVersion -m $tagMessage
    
    Write-Color "[OK] Tag $newVersion created!" "Green"
    
    # Step 12: Push everything
    Write-Host ""
    Write-Color "[*] Pushing CHANGELOG commit..." "Blue"
    git push origin master
    
    Write-Color "[*] Pushing tag $newVersion..." "Blue"
    git push origin $newVersion
    
    # Step 13: Success message
    Write-Host ""
    Write-Color "====================================" "Green"
    Write-Color " Release $newVersion Created!" "Green"
    Write-Color "====================================" "Green"
    Write-Host ""
    Write-Color "[OK] Tag: $newVersion" "Green"
    Write-Color "[OK] Released at: $timestamp" "Green"
    Write-Color "[OK] From: master branch" "Green"
    Write-Host ""
    Write-Color "[*] GitHub Actions will auto-create release page" "Cyan"
    Write-Host ""
    Write-Host "View release at:"
    Write-Host "https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/releases/tag/$newVersion" -ForegroundColor Cyan
    
} catch {
    Write-Color "[X] Error: $_" "Red"
    exit 1
} finally {
    # Always return to original branch
    Write-Host ""
    Write-Color "[*] Returning to $originalBranch..." "Blue"
    git checkout $originalBranch
}

Write-Host ""
Write-Color "[OK] Done!" "Green"
