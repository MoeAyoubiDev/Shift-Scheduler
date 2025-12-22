#!/bin/bash
# Quick update script - Pull and deploy in one command
# Usage: ./update.sh

cd "$(dirname "$0")"

echo "🔄 Updating application..."
echo ""

# Pull latest changes
if [ -d ".git" ]; then
    echo "📥 Pulling latest changes from git..."
    git pull
    echo ""
else
    echo "⚠️  Warning: Not a git repository. Skipping git pull."
    echo ""
fi

# Run deployment
echo "🚀 Running deployment..."
./deploy.sh

