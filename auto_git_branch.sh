#!/usr/bin/env bash

# ------------------------------------------------------------
# Auto‑branch & push script for the Toko SS POS repository
# ------------------------------------------------------------
# Usage:
#   ./auto_git_branch.sh   # run from the project root
# ------------------------------------------------------------
# What it does:
# 1. Checks for uncommitted changes.
# 2. If changes exist, creates a new branch named
#    "auto/$(date +%Y%m%d%H%M%S)".
# 3. Stages all changes, commits with a timestamped message,
#    and pushes to the remote origin, setting upstream tracking.
# ------------------------------------------------------------

set -euo pipefail

# Ensure we are inside a git repository
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo "Error: Not a git repository."
    exit 1
fi

# Detect uncommitted changes
if [[ -z "$(git status --porcelain)" ]]; then
    echo "No changes to commit. Exiting."
    exit 0
fi

# Generate a unique branch name
BRANCH_NAME="auto/$(date +%Y%m%d%H%M%S)"

# Create and switch to the new branch
git checkout -b "$BRANCH_NAME"

# Stage all modifications
git add .

# Commit with a timestamped message
COMMIT_MSG="Auto‑commit on $(date '+%Y-%m-%d %H:%M:%S')"
git commit -m "$COMMIT_MSG"

# Push the new branch to origin and set upstream
git push -u origin "$BRANCH_NAME"

echo "✅ Changes committed and pushed to branch '$BRANCH_NAME' on GitHub."
