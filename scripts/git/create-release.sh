#!/bin/bash

# Create Release Script with Auto-Generated Release Notes
# Usage: ./scripts/create-release.sh

set -e

GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
NC='\033[0m'

echo -e "${BLUE}====================================${NC}"
echo -e "${BLUE} Create Release v2.0 (Auto-Gen)${NC}"
echo -e "${BLUE}====================================${NC}"
echo ""

ORIGINAL_BRANCH=$(git branch --show-current)
echo -e "${CYAN}[*] Current branch: ${YELLOW}${ORIGINAL_BRANCH}${NC}"

cleanup() {
    echo ""
    echo -e "${BLUE}[*] Returning to ${ORIGINAL_BRANCH}...${NC}"
    git checkout "$ORIGINAL_BRANCH" 2>/dev/null || true
}

trap cleanup EXIT

# Update branches
echo ""
echo -e "${BLUE}[*] Updating develop...${NC}"
git checkout develop
git pull origin develop

echo ""
echo -e "${BLUE}[*] Updating master...${NC}"
git checkout master
git pull origin master

# Merge
echo ""
echo -e "${YELLOW}[*] Merging develop into master...${NC}"
if ! git merge develop --no-ff -m "chore: merge develop into master for release"; then
    echo -e "${RED}[X] Merge failed! Resolve conflicts and try again.${NC}"
    exit 1
fi

echo -e "${GREEN}[OK] Merge successful!${NC}"
git push origin master

# Detect latest tag
echo ""
echo -e "${CYAN}[*] Fetching latest tags from remote...${NC}"
git fetch --tags --force

echo -e "${CYAN}[*] Detecting latest tag...${NC}"

LATEST_TAG=$(git describe --tags --abbrev=0 2>/dev/null || echo "")

if [[ -z "$LATEST_TAG" ]]; then
    LATEST_TAG="v0.0.0"
    echo -e "${YELLOW}[!] No tags found, starting from v0.0.0${NC}"
else
    echo -e "${GREEN}[*] Latest tag: ${LATEST_TAG}${NC}"
fi

# Parse version
VERSION_NUM="${LATEST_TAG#v}"
IFS='.' read -r MAJOR MINOR PATCH <<< "$VERSION_NUM"

# Select bump
echo ""
echo -e "${YELLOW}Current version: ${LATEST_TAG}${NC}"
echo ""
echo "Select version bump:"
echo "1) PATCH   (${LATEST_TAG} -> v${MAJOR}.${MINOR}.$((PATCH+1))) - Bug fixes"
echo "2) MINOR   (${LATEST_TAG} -> v${MAJOR}.$((MINOR+1)).0) - New features"
echo "3) MAJOR   (${LATEST_TAG} -> v$((MAJOR+1)).0.0) - Breaking changes"
echo "4) Custom version"
echo "5) Cancel"
echo ""
read -p "Select [1-5]: " CHOICE

NEW_VERSION=""
case $CHOICE in
    1) NEW_VERSION="v${MAJOR}.${MINOR}.$((PATCH+1))" ;;
    2) NEW_VERSION="v${MAJOR}.$((MINOR+1)).0" ;;
    3) NEW_VERSION="v$((MAJOR+1)).0.0" ;;
    4)
        read -p "Enter version (e.g., 1.5.0): " CUSTOM
        NEW_VERSION="v${CUSTOM}"
        ;;
    *)
        echo -e "${YELLOW}[!] Cancelled${NC}"
        exit 0
        ;;
esac

echo ""
echo -e "${GREEN}[*] New version: ${NEW_VERSION}${NC}"

# Auto-generate release notes
echo ""
echo -e "${CYAN}[*] Auto-generating release notes from commits...${NC}"

if [[ "$LATEST_TAG" != "v0.0.0" ]]; then
    COMMITS=$(git log "${LATEST_TAG}..HEAD" --pretty=format:"%s|||%an" --no-merges)
else
    COMMITS=$(git log --pretty=format:"%s|||%an" --no-merges -20)
fi

# Categorize commits
FEATURES=()
FIXES=()
DOCS=()
OTHERS=()
BREAKING=()

while IFS= read -r commit; do
    [[ -z "$commit" ]] && continue
    
    MSG="${commit%%|||*}"
    
    if [[ "$MSG" =~ ^feat[\(\!]|^feat: ]]; then
        CLEAN=$(echo "$MSG" | sed -E 's/^feat(\([^)]*\)|!)?:?[[:space:]]*//')
        FEATURES+=("- $CLEAN")
    elif [[ "$MSG" =~ ^fix[\(\!]|^fix: ]]; then
        CLEAN=$(echo "$MSG" | sed -E 's/^fix(\([^)]*\)|!)?:?[[:space:]]*//')
        FIXES+=("- $CLEAN")
    elif [[ "$MSG" =~ ^docs[\(\!]|^docs: ]]; then
        CLEAN=$(echo "$MSG" | sed -E 's/^docs(\([^)]*\)|!)?:?[[:space:]]*//')
        DOCS+=("- $CLEAN")
    else
        CLEAN=$(echo "$MSG" | sed -E 's/^[a-z]+(\([^)]*\)|!)?:?[[:space:]]*//')
        OTHERS+=("- $CLEAN")
    fi
    
    if [[ "$MSG" =~ ! ]] || [[ "$MSG" =~ BREAKING\ CHANGE ]]; then
        BREAKING+=("- $MSG")
    fi
done <<< "$COMMITS"

COMMIT_COUNT=$(echo "$COMMITS" | wc -l | tr -d ' ')

# Get release title
echo ""
echo -e "${CYAN}[*] Enter release title (or press Enter for auto-title):${NC}"
read -p "> " RELEASE_TITLE

if [[ -z "$RELEASE_TITLE" ]]; then
    RELEASE_TITLE="Release ${NEW_VERSION}"
fi

# Generate timestamp
TIMESTAMP=$(date '+%d/%m/%Y - %Hh%M')

# Build CHANGELOG entry
CHANGELOG_ENTRY="## ${TIMESTAMP}

### ${RELEASE_TITLE}

"

if [[ ${#FEATURES[@]} -gt 0 ]]; then
    CHANGELOG_ENTRY+="**✨ New Features:**
"
    for feature in "${FEATURES[@]}"; do
        CHANGELOG_ENTRY+="${feature}
"
    done
    CHANGELOG_ENTRY+="
"
fi

if [[ ${#FIXES[@]} -gt 0 ]]; then
    CHANGELOG_ENTRY+="**🐛 Bug Fixes:**
"
    for fix in "${FIXES[@]}"; do
        CHANGELOG_ENTRY+="${fix}
"
    done
    CHANGELOG_ENTRY+="
"
fi

if [[ ${#DOCS[@]} -gt 0 ]]; then
    CHANGELOG_ENTRY+="**📚 Documentation:**
"
    for doc in "${DOCS[@]}"; do
        CHANGELOG_ENTRY+="${doc}
"
    done
    CHANGELOG_ENTRY+="
"
fi

if [[ ${#OTHERS[@]} -gt 0 ]]; then
    CHANGELOG_ENTRY+="**🔧 Other Changes:**
"
    for other in "${OTHERS[@]}"; do
        CHANGELOG_ENTRY+="${other}
"
    done
    CHANGELOG_ENTRY+="
"
fi

if [[ ${#BREAKING[@]} -gt 0 ]]; then
    CHANGELOG_ENTRY+="**⚠️ BREAKING CHANGES:**
"
    for breaking in "${BREAKING[@]}"; do
        CHANGELOG_ENTRY+="${breaking}
"
    done
    CHANGELOG_ENTRY+="
"
fi

CHANGELOG_ENTRY+="**Technical Details:**
- Tag: ${NEW_VERSION}
- Commits: ${COMMIT_COUNT}
- Released from: master branch
- Release URL: https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/releases/tag/${NEW_VERSION}

---

"

# Show preview
echo ""
echo -e "${YELLOW}====================================${NC}"
echo -e "${YELLOW} CHANGELOG Preview${NC}"
echo -e "${YELLOW}====================================${NC}"
echo -e "$CHANGELOG_ENTRY"
echo -e "${YELLOW}====================================${NC}"
echo ""

read -p "Create release with auto-generated notes? [Y/n]: " CONFIRM

if [[ "$CONFIRM" =~ ^[Nn]$ ]]; then
    echo -e "${YELLOW}[!] Cancelled${NC}"
    exit 0
fi

# Update CHANGELOG.md
echo ""
echo -e "${BLUE}[*] Updating CHANGELOG.md...${NC}"

if [[ -f "CHANGELOG.md" ]]; then
    EXISTING=$(cat CHANGELOG.md)
    echo -e "# CHANGELOG\n\n${CHANGELOG_ENTRY}\n${EXISTING}" > CHANGELOG.md
else
    echo -e "# CHANGELOG\n\nAll notable changes to CityResQ360-DTUDZ will be documented in this file.\n\n${CHANGELOG_ENTRY}" > CHANGELOG.md
fi

echo -e "${GREEN}[OK] CHANGELOG.md updated!${NC}"

# Commit
git add CHANGELOG.md
git commit -m "docs: update CHANGELOG for ${NEW_VERSION}"

# Create tag
echo ""
echo -e "${BLUE}[*] Creating tag ${NEW_VERSION}...${NC}"

TAG_MSG="${RELEASE_TITLE}

Auto-generated from commits.
Released: ${TIMESTAMP}"

echo -e "$TAG_MSG" | git tag -a "$NEW_VERSION" -F -

echo -e "${GREEN}[OK] Tag created!${NC}"

# Push
echo ""
echo -e "${BLUE}[*] Pushing changes...${NC}"
git push origin master
git push origin "$NEW_VERSION"

# Success
echo ""
echo -e "${GREEN}====================================${NC}"
echo -e "${GREEN} Release ${NEW_VERSION} Created!${NC}"
echo -e "${GREEN}====================================${NC}"
echo ""
echo -e "${GREEN}[OK] Tag: ${NEW_VERSION}${NC}"
echo -e "${GREEN}[OK] Released at: ${TIMESTAMP}${NC}"
echo -e "${GREEN}[OK] Commits included: ${COMMIT_COUNT}${NC}"
echo ""
echo -e "${CYAN}[*] GitHub Actions will create release page${NC}"
echo ""
echo -e "${CYAN}https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/releases/tag/${NEW_VERSION}${NC}"

echo ""
echo -e "${GREEN}[OK] Done!${NC}"
