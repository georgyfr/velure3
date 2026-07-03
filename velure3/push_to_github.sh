#!/bin/bash
# Push Velure3 theme to GitHub
# Usage: ./push_to_github.sh YOUR_GITHUB_TOKEN

TOKEN="${1:?Usage: $0 YOUR_GITHUB_TOKEN}"

REPO_DIR="/home/z/my-project/velure3"
cd "$REPO_DIR"

# Create the repo on GitHub (will fail silently if already exists)
export PATH="/home/z/.local/bin:$PATH"
gh auth login --with-token <<< "$TOKEN" 2>/dev/null

echo "Creation du depot GitHub georgyfr/velure3..."
gh repo create georgyfr/velure3 --public --description "Velure3 - Theme WordPress FSE Mode & E-Commerce" --source=. --push=false 2>&1 || echo "(Depot peut deja exister)"

# Add remote with token
git remote remove origin 2>/dev/null
git remote add origin "https://${TOKEN}@github.com/georgyfr/velure3.git"

# Push all commits and tags
echo "Push des commits et tags..."
git push -u origin main 2>&1
git push origin --tags 2>&1

# Remove token from remote URL for security
git remote set-url origin "https://github.com/georgyfr/velure3.git"

echo ""
echo "=== PUSH COMPLETE ==="
echo "Repo : https://github.com/georgyfr/velure3"
echo "Tags : $(git tag -l | tr '\n' ', ')"
echo "Token retire de la config git."
echo ""
echo "Le document ETAPES_DEVELOPPEMENT.txt est visible a la racine du repo."