#!/bin/bash
set -e

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Run seeds if the environment variable is set
if [ "$RUN_SEEDS" = "true" ]; then
    echo "Running seeds..."
    php artisan db:seed --force
fi

# Start Apache
echo "Starting Apache..."
exec apache2-foreground
