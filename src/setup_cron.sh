#!/bin/bash

# XKCD CRON Job Setup Script
# This script automatically configures a CRON job to run cron.php every 24 hours

echo "🚀 Setting up XKCD CRON job..."

# Get the absolute path to the current directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CRON_PHP_PATH="$SCRIPT_DIR/cron.php"

# Check if cron.php exists
if [ ! -f "$CRON_PHP_PATH" ]; then
    echo "❌ Error: cron.php not found at $CRON_PHP_PATH"
    exit 1
fi

# Find PHP executable
PHP_PATH=$(which php)
if [ -z "$PHP_PATH" ]; then
    echo "❌ Error: PHP not found in PATH"
    exit 1
fi

echo "📍 Found PHP at: $PHP_PATH"
echo "📍 CRON script path: $CRON_PHP_PATH"

# Create the CRON job command
# This will run every day at 9:00 AM
CRON_COMMAND="0 9 * * * $PHP_PATH $CRON_PHP_PATH >> $SCRIPT_DIR/cron.log 2>&1"

# Get current crontab
TEMP_CRON=$(mktemp)
crontab -l > "$TEMP_CRON" 2>/dev/null || true

# Check if CRON job already exists
if grep -q "$CRON_PHP_PATH" "$TEMP_CRON" 2>/dev/null; then
    echo "⚠️  CRON job for XKCD already exists. Removing old entry..."
    grep -v "$CRON_PHP_PATH" "$TEMP_CRON" > "${TEMP_CRON}.new"
    mv "${TEMP_CRON}.new" "$TEMP_CRON"
fi

# Add new CRON job
echo "$CRON_COMMAND" >> "$TEMP_CRON"

# Install the new crontab
crontab "$TEMP_CRON"

# Clean up
rm "$TEMP_CRON"

echo "✅ CRON job successfully configured!"
echo "📅 The job will run daily at 9:00 AM"
echo "📝 Logs will be written to: $SCRIPT_DIR/cron.log"
echo ""
echo "To verify the CRON job was added, run: crontab -l"
echo "To test the CRON job manually, run: php $CRON_PHP_PATH"
echo ""
echo "🎉 Setup complete! Daily XKCD comics will be sent to all subscribers."

# Make the script executable
chmod +x "$0"

# Test the CRON script to ensure it works
echo "🧪 Testing CRON script..."
if $PHP_PATH "$CRON_PHP_PATH"; then
    echo "✅ CRON script test successful!"
else
    echo "⚠️  CRON script test failed. Please check the logs at $SCRIPT_DIR/cron.log"
fi