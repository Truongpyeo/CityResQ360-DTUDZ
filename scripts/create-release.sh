#!/bin/bash

# Create Release Script - Auto merge develop to master and create release
# Usage: ./scripts/create-release.sh

set -e

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${BLUE}====================================${NC}"
echo -e "${BLUE} Create Release v1.0${NC}"
echo -e "${BLUE}====================================${NC}"
echo ""

# Save current branch
ORIGINAL_BRANCH=$(git branch --show-current)
echo -e "${CYAN}[*] Current branch: ${YELLOW}${ORIGINAL_BRANCH}${NC}"

# Function to cleanup on error
cleanup() {
    echo ""
    echo -e "${BLUE}[*] Returning to ${ORIGINAL_BRANCH}...${NC}"
    git checkout "$ORIGINAL_BRANCH" 2>/dev/null || true
}

trap cleanup EXIT

# Step 1: Checkout and update develop
echo ""
echo -e "${BLUE}[*] Checking out develop branch...${NC}"
git checkout develop

echo -e "${BLUE}[*] Pulling latest develop...${NC}"
git pull origin develop

# Step 2: Checkout and update master
echo ""
echo -e "${BLUE}[*] Checking out master branch...${NC}"
git checkout master

echo -e "${BLUE}[*] Pulling latest master...${NC}"
git pull origin master

# Step 3: Merge develop into master
echo ""
echo -e "${YELLOW}[*] Merging develop into master...${NC}"
if ! git merge develop --no-ff -m "chore: merge develop into master for release"; then
    echo -e "${RED}[X] Merge failed! Please resolve conflicts manually.${NC}"
    exit 1
fi

echo -e "${GREEN}[OK] Merge successful!${NC}"

# Step 4: Push master
echo -e "${BLUE}[*] Pushing master...${NC}"
git push origin master

# Step 5: Detect latest tag
echo ""
echo -e "${CYAN}[*] Detecting latest tag...${NC}"

LATEST_TAG=$(git describe --tags --abbrev=0 2>/dev/null || echo "")

if [[ -z "$LATEST_TAG" ]]; then
    LATEST_TAG="v0.0.0"
    echo -e "${YELLOW}[!] No existing tags found, starting from v0.0.0${NC}"
else
    echo -e "${GREEN}[*] Latest tag: ${LATEST_TAG}${NC}"
fi

# Parse version
VERSION_NUM="${LATEST_TAG#v}"
IFS='.' read -r MAJOR MINOR PATCH <<< "$VERSION_NUM"

# Step 6: Ask for version bump
echo ""
echo -e "${YELLOW}Current version: ${LATEST_TAG}${NC}"
echo ""
echo "Select version bump:"
echo "1) PATCH   (${LATEST_TAG} → v${MAJOR}.${MINOR}.$((PATCH+1))) - Bug fixes"
echo "2) MINOR   (${LATEST_TAG} → v${MAJOR}.$((MINOR+1)).0) - New features"
echo "3) MAJOR   (${LATEST_TAG} → v$((MAJOR+1)).0.0) - Breaking changes"
echo "4) Custom  - Enter version manually"
echo "5) Cancel"
echo ""
read -p "Select [1-5]: " CHOICE

NEW_VERSION=""
case $CHOICE in
    1) NEW_VERSION="v${MAJOR}.${MINOR}.$((PATCH+1))" ;;
    2) NEW_VERSION="v${MAJOR}.$((MINOR+1)).0" ;;
    3) NEW_VERSION="v$((MAJOR+1)).0.0" ;;
    4)
        read -p "Enter version (e.g., 1.5.0): " CUSTOM_VER
        NEW_VERSION="v${CUSTOM_VER}"
        ;;
    *)
        echo -e "${YELLOW}[!] Cancelled${NC}"
        exit 0
        ;;
esac

echo ""
echo -e "${GREEN}[*] New version: ${NEW_VERSION}${NC}"

# Step 7: Get release notes
echo ""
echo -e "${CYAN}[*] Enter release title (one line):${NC}"
read -p "> " RELEASE_TITLE

if [[ -z "$RELEASE_TITLE" ]]; then
    RELEASE_TITLE="Release ${NEW_VERSION}"
fi

echo ""
echo -e "${CYAN}[*] Enter release notes (press Enter twice to finish):${NC}"
RELEASE_NOTES=""
EMPTY_COUNT=0

while IFS= read -r line; do
    if [[ -z "$line" ]]; then
        ((EMPTY_COUNT++))
        if [[ $EMPTY_COUNT -ge 2 ]]; then
            break
        fi
    else
        EMPTY_COUNT=0
        RELEASE_NOTES="${RELEASE_NOTES}- ${line}\n"
    fi
done

# Step 8: Generate timestamp
TIMESTAMP=$(date '+%d/%m/%Y - %Hh%M')

# Step 9: Update CHANGELOG.md
echo ""
echo -e "${BLUE}[*] Updating CHANGELOG.md...${NC}"

CHANGELOG_PATH="CHANGELOG.md"

# Build new entry
NEW_ENTRY="## ${TIMESTAMP}

### ${RELEASE_TITLE}

"

if [[ -n "$RELEASE_NOTES" ]]; then
    NEW_ENTRY="${NEW_ENTRY}${RELEASE_NOTES}
"
fi

NEW_ENTRY="${NEW_ENTRY}
**Technical Details:**
- Tag: ${NEW_VERSION}
- Released from: master branch
- Release URL: https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/releases/tag/${NEW_VERSION}

---

"

# Read existing CHANGELOG or create new
if [[ -f "$CHANGELOG_PATH" ]]; then
    EXISTING_CONTENT=$(cat "$CHANGELOG_PATH")
    echo -e "# CHANGELOG\n\n${NEW_ENTRY}\n${EXISTING_CONTENT}" > "$CHANGELOG_PATH"
else
    echo -e "# CHANGELOG

All notable changes to CityResQ360-DTUDZ will be documented in this file.

${NEW_ENTRY}" > "$CHANGELOG_PATH"
fi

echo -e "${GREEN}[OK] CHANGELOG.md updated!${NC}"

# Step 10: Commit CHANGELOG
echo -e "${BLUE}[*] Committing CHANGELOG...${NC}"
git add CHANGELOG.md
git commit -m "docs: update CHANGELOG for ${NEW_VERSION}"

# Step 11: Create tag
echo ""
echo -e "${BLUE}[*] Creating tag ${NEW_VERSION}...${NC}"

TAG_MESSAGE="${RELEASE_TITLE}

${RELEASE_NOTES}
Released: ${TIMESTAMP}
From: master branch"

echo -e "$TAG_MESSAGE" | git tag -a "$NEW_VERSION" -F -

echo -e "${GREEN}[OK] Tag ${NEW_VERSION} created!${NC}"

# Step 12: Push everything
echo ""
echo -e "${BLUE}[*] Pushing CHANGELOG commit...${NC}"
git push origin master

echo -e "${BLUE}[*] Pushing tag ${NEW_VERSION}...${NC}"
git push origin "$NEW_VERSION"

# Step 13: Success message
echo ""
echo -e "${GREEN}====================================${NC}"
echo -e "${GREEN} Release ${NEW_VERSION} Created!${NC}"
echo -e "${GREEN}====================================${NC}"
echo ""
echo -e "${GREEN}[OK] Tag: ${NEW_VERSION}${NC}"
echo -e "${GREEN}[OK] Released at: ${TIMESTAMP}${NC}"
echo -e "${GREEN}[OK] From: master branch${NC}"
echo ""
echo -e "${CYAN}[*] GitHub Actions will auto-create release page${NC}"
echo ""
echo "View release at:"
echo -e "${CYAN}https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/releases/tag/${NEW_VERSION}${NC}"

echo ""
echo -e "${GREEN}[OK] Done!${NC}"
