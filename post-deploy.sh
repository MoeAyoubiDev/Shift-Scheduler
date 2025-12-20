#!/bin/bash
# Post-deploy hook - Automatically runs after git pull
# This script can be called from a git hook or run manually
# To set up: Add to .git/hooks/post-merge (chmod +x .git/hooks/post-merge)

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

# Run the main deployment script with skip-git flag (since we just pulled)
exec "$SCRIPT_DIR/deploy.sh" --skip-git

