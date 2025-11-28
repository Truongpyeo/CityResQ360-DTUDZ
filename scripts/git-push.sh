#!/bin/bash

# 🚀 Git Auto Push Script - Interactive Conventional Commits v2.0
# Usage: ./scripts/git-push.sh
# Supports: Scopes, Breaking Changes, Multi-line messages

set -e

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Print header
echo -e "${BLUE}================================${NC}"
echo -e "${BLUE}🚀 Git Auto Push v2.0${NC}"
echo -e "${BLUE}================================${NC}"
echo ""

# Get current branch
CURRENT_BRANCH=$(git branch --show-current)
echo -e "${GREEN}📍 Current branch: ${YELLOW}${CURRENT_BRANCH}${NC}"
echo ""

# Check for uncommitted changes
if [[ -z $(git status -s) ]]; then
    echo -e "${RED}⚠️  No changes to commit!${NC}"
    exit 0
fi

# Show changed files
echo -e "${BLUE}📝 Changed files:${NC}"
git status --short
echo ""

# Select commit type
echo -e "${BLUE}Select commit type:${NC}"
echo "1) ✨ feat      - New feature"
echo "2) 🐛 fix       - Bug fix"
echo "3) 📚 docs      - Documentation"
echo "4) 💄 style     - Code style (formatting)"
echo "5) ♻️  refactor - Code refactoring"
echo "6) ⚡ perf      - Performance improvement"
echo "7) ✅ test      - Add/update tests"
echo "8) 🔨 build     - Build system changes"
echo "9) 👷 ci        - CI/CD changes"
echo "10) 🔧 chore    - Maintenance tasks"
echo ""
read -p "Enter choice [1-10]: " TYPE_CHOICE

case $TYPE_CHOICE in
    1) TYPE="feat" ;;
    2) TYPE="fix" ;;
    3) TYPE="docs" ;;
    4) TYPE="style" ;;
    5) TYPE="refactor" ;;
    6) TYPE="perf" ;;
    7) TYPE="test" ;;
    8) TYPE="build" ;;
    9) TYPE="ci" ;;
    10) TYPE="chore" ;;
    *) echo -e "${RED}Invalid choice!${NC}"; exit 1 ;;
esac

# Optional scope
echo ""
read -p "Scope (optional, e.g., auth, api, docker): " SCOPE

# Commit message (header)
echo ""
echo -e "${CYAN}📝 Commit header (short description):${NC}"
read -p "> " MESSAGE

if [[ -z "$MESSAGE" ]]; then
    echo -e "${RED}❌ Message cannot be empty!${NC}"
    exit 1
fi

# Optional body (multi-line)
echo ""
echo -e "${CYAN}📄 Commit body (optional, multi-line. Press Enter twice to finish):${NC}"
BODY=""
EMPTY_COUNT=0
while IFS= read -r line; do
    if [[ -z "$line" ]]; then
        ((EMPTY_COUNT++))
        if [[ $EMPTY_COUNT -ge 2 ]]; then
            break
        fi
        BODY="${BODY}\n"
    else
        EMPTY_COUNT=0
        BODY="${BODY}${line}\n"
    fi
done

# Breaking change check
echo ""
read -p "Is this a BREAKING CHANGE? [y/N]: " IS_BREAKING

BREAKING_CHANGE=""
if [[ "$IS_BREAKING" =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}⚠️  Describe the breaking change (multi-line, press Enter twice to finish):${NC}"
    BREAKING_DESC=""
    EMPTY_COUNT=0
    while IFS= read -r line; do
        if [[ -z "$line" ]]; then
            ((EMPTY_COUNT++))
            if [[ $EMPTY_COUNT -ge 2 ]]; then
                break
            fi
            BREAKING_DESC="${BREAKING_DESC}\n"
        else
            EMPTY_COUNT=0
            if [[ -z "$BREAKING_DESC" ]]; then
                BREAKING_DESC="$line"
            else
                BREAKING_DESC="${BREAKING_DESC}\n${line}"
            fi
        fi
    done
    if [[ -n "$BREAKING_DESC" ]]; then
        BREAKING_CHANGE="\n\nBREAKING CHANGE: ${BREAKING_DESC}"
        # Add ! to header for breaking changes
        if [[ -z "$SCOPE" ]]; then
            COMMIT_HEADER="${TYPE}!: ${MESSAGE}"
        else
            COMMIT_HEADER="${TYPE}(${SCOPE})!: ${MESSAGE}"
        fi
    fi
else
    # Build normal commit header
    if [[ -z "$SCOPE" ]]; then
        COMMIT_HEADER="${TYPE}: ${MESSAGE}"
    else
        COMMIT_HEADER="${TYPE}(${SCOPE}): ${MESSAGE}"
    fi
fi

# Build full commit message
COMMIT_MSG="${COMMIT_HEADER}"
if [[ -n "$BODY" ]]; then
    COMMIT_MSG="${COMMIT_MSG}\n\n${BODY}"
fi
if [[ -n "$BREAKING_CHANGE" ]]; then
    COMMIT_MSG="${COMMIT_MSG}${BREAKING_CHANGE}"
fi

# Show preview
echo ""
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}📋 Commit message preview:${NC}"
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}${COMMIT_MSG}${NC}" | sed 's/\\n/\n/g'
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

# Confirm
read -p "Continue? [Y/n]: " CONFIRM

if [[ "$CONFIRM" =~ ^[Nn]$ ]]; then
    echo -e "${RED}Cancelled.${NC}"
    exit 0
fi

# Ask if merge to develop
AUTO_MERGE="n"
if [[ "$CURRENT_BRANCH" != "develop" && "$CURRENT_BRANCH" != "main" && "$CURRENT_BRANCH" != "master" ]]; then
    echo ""
    read -p "Auto-merge to develop after push? [y/N]: " AUTO_MERGE
fi

# Execute git commands
echo ""
echo -e "${BLUE}📦 Adding files...${NC}"
git add .

echo -e "${BLUE}💾 Committing...${NC}"
# Use -e flag to interpret escape sequences
echo -e "$COMMIT_MSG" | git commit -F -

echo -e "${BLUE}📤 Pushing to ${YELLOW}${CURRENT_BRANCH}${NC}..."
git push origin "$CURRENT_BRANCH"

echo ""
echo -e "${GREEN}✅ Successfully pushed to ${CURRENT_BRANCH}!${NC}"
echo -e "${GREEN}📝 Commit header: ${COMMIT_HEADER}${NC}"

# Show if breaking change
if [[ -n "$BREAKING_CHANGE" ]]; then
    echo -e "${RED}⚠️  BREAKING CHANGE committed!${NC}"
fi

# Auto-merge to develop
echo ""
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
read -p "Merge into develop branch? [Y/n]: " MERGE_DEVELOP

if [[ ! "$MERGE_DEVELOP" =~ ^[Nn]$ ]]; then
    # Check if develop branch exists
    if ! git show-ref --verify --quiet refs/heads/develop; then
        echo -e "${YELLOW}⚠️  develop branch doesn't exist locally${NC}"
        read -p "Create develop branch from main? [Y/n]: " CREATE_DEVELOP
        
        if [[ ! "$CREATE_DEVELOP" =~ ^[Nn]$ ]]; then
            git checkout -b develop main
            git push -u origin develop
            echo -e "${GREEN}✅ Created develop branch${NC}"
        else
            echo -e "${YELLOW}Skipping merge to develop${NC}"
            exit 0
        fi
    fi
    
    echo ""
    echo -e "${BLUE}🔀 Merging into develop...${NC}"
    
    # Save current branch
    SOURCE_BRANCH="$CURRENT_BRANCH"
    
    # Checkout develop
    git checkout develop
    
    # Pull latest develop
    echo -e "${BLUE}📥 Pulling latest develop...${NC}"
    git pull origin develop
    
    # Merge current branch into develop
    echo -e "${BLUE}🔀 Merging ${YELLOW}${SOURCE_BRANCH}${NC} into ${YELLOW}develop${NC}...${NC}"
    
    if git merge "$SOURCE_BRANCH" --no-edit; then
        echo -e "${GREEN}✅ Merge successful!${NC}"
        
        # Push develop
        echo -e "${BLUE}📤 Pushing develop...${NC}"
        git push origin develop
        
        echo -e "${GREEN}✅ develop branch updated!${NC}"
    else
        echo -e "${RED}❌ Merge conflict detected!${NC}"
        echo -e "${YELLOW}Please resolve conflicts manually:${NC}"
        echo -e "${CYAN}  1. Fix conflicts in the files${NC}"
        echo -e "${CYAN}  2. git add <resolved-files>${NC}"
        echo -e "${CYAN}  3. git commit${NC}"
        echo -e "${CYAN}  4. git push origin develop${NC}"
        echo -e "${CYAN}  5. git checkout ${SOURCE_BRANCH}${NC}"
        exit 1
    fi
    
    # Return to original branch
    echo ""
    echo -e "${BLUE}🔙 Returning to ${YELLOW}${SOURCE_BRANCH}${NC}...${NC}"
    git checkout "$SOURCE_BRANCH"
    
    echo ""
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${GREEN}🎉 All done!${NC}"
    echo -e "${GREEN}✅ Pushed to: ${SOURCE_BRANCH}${NC}"
    echo -e "${GREEN}✅ Merged to: develop${NC}"
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
else
    echo -e "${YELLOW}Skipped merge to develop${NC}"
fi

