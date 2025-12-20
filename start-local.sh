#!/bin/bash

# Quick Start Script for Local Testing
# Shift Scheduler System

echo "🚀 Starting Shift Scheduler Local Server..."
echo ""

# Check if database exists
echo "📊 Checking database..."
mysql -u root -e "USE ShiftSchedulerDB;" 2>/dev/null
if [ $? -ne 0 ]; then
    echo "⚠️  Database not found. Setting up database..."
    echo "   Please run these commands first:"
    echo "   mysql -u root -p < database/schema.sql"
    echo "   mysql -u root -p ShiftSchedulerDB < database/stored_procedures_fixed.sql"
    echo ""
    read -p "Press Enter to continue anyway, or Ctrl+C to exit..."
fi

# Check config
if [ ! -f "config/database.php" ]; then
    echo "⚠️  config/database.php not found!"
    exit 1
fi

echo "✅ Starting PHP development server..."
echo "📍 Server will be available at: http://localhost:8000"
echo "📍 Press Ctrl+C to stop the server"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Start PHP server
php -S localhost:8000 -t public

