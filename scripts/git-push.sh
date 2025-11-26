#!/bin/bash

# 🚀 Git Auto Push Script - Interactive Conventional Commits
# Usage: ./scripts/git-push.sh

set -e

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Print header
echo -e "${BLUE}================================${NC}"
echo -e "${BLUE}🚀 Git Auto Push${NC}"
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
read -p "Scope (optional, e.g., auth, api, mobile): " SCOPE

# Commit message
echo ""
read -p "Commit message: " MESSAGE

if [[ -z "$MESSAGE" ]]; then
    echo -e "${RED}❌ Message cannot be empty!${NC}"
    exit 1
fi

# Build commit message
if [[ -z "$SCOPE" ]]; then
    COMMIT_MSG="${TYPE}: ${MESSAGE}"
else
    COMMIT_MSG="${TYPE}(${SCOPE}): ${MESSAGE}"
fi

# Confirm
echo ""
echo -e "${YELLOW}Commit message will be:${NC}"
echo -e "${GREEN}${COMMIT_MSG}${NC}"
echo ""
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
git commit -m "$COMMIT_MSG"

echo -e "${BLUE}📤 Pushing to ${YELLOW}${CURRENT_BRANCH}${NC}..."
git push origin "$CURRENT_BRANCH"

echo ""
echo -e "${GREEN}✅ Successfully pushed to ${CURRENT_BRANCH}!${NC}"
echo -e "${GREEN}📝 Commit: ${COMMIT_MSG}${NC}"

# Auto merge to develop if requested
if [[ "$AUTO_MERGE" =~ ^[Yy]$ ]]; then
    echo ""
    echo -e "${BLUE}🔀 Merging to develop...${NC}"
    
    # Save current branch
    FEATURE_BRANCH="$CURRENT_BRANCH"
    
    # Checkout develop and pull latest
    git checkout develop
    git pull origin develop
    
    # Merge feature branch
    echo -e "${BLUE}Merging ${YELLOW}${FEATURE_BRANCH}${NC} into ${YELLOW}develop${NC}..."
    
    if git merge "$FEATURE_BRANCH" --no-edit; then
        echo -e "${GREEN}✅ Merge successful!${NC}"
        
        # Push develop
        echo -e "${BLUE}📤 Pushing develop...${NC}"
        git push origin develop
        
        echo ""
        echo -e "${GREEN}✅ Successfully merged to develop and pushed!${NC}"
        echo -e "${YELLOW}Staying on develop branch${NC}"
    else
        echo ""
        echo -e "${RED}❌ Merge conflict detected!${NC}"
        echo -e "${YELLOW}Please resolve conflicts manually:${NC}"
        echo "  1. Fix conflicts in the files"
        echo "  2. git add <resolved-files>"
        echo "  3. git commit"
        echo "  4. git push origin develop"
        echo ""
        echo -e "${YELLOW}Or abort merge:${NC}"
        echo "  git merge --abort"
        echo "  git checkout $FEATURE_BRANCH"
    fi
fi
