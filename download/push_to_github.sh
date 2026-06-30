#!/bin/bash
# Push NutriVitaX Pro theme to GitHub
# Usage: ./push_to_github.sh YOUR_GITHUB_TOKEN

TOKEN="${1:?Usage: $0 YOUR_GITHUB_TOKEN}"

REPO_DIR="/home/z/my-project/lampstack-wordpress"
cd "$REPO_DIR"

# Add remote with token
git remote remove origin 2>/dev/null
git remote add origin "https://${TOKEN}@github.com/georgyfr/lampstack-wordpress.git"

# Fetch and merge with existing remote history
git fetch origin main 2>/dev/null
if [ $? -eq 0 ]; then
    # Rebase local commit onto remote history
    git rebase origin/main
else
    echo "Note: Could not fetch remote (repo may be empty or token issue)"
fi

# Push to GitHub
git push -u origin main 2>&1

# Remove token from remote URL
git remote set-url origin "https://github.com/georgyfr/lampstack-wordpress.git"

echo ""
echo "Push complete! Token removed from git config."
echo "View at: https://github.com/georgyfr/lampstack-wordpress"
