#!/bin/bash
set -e

echo "=========================================================="
echo "  DARK MATTER — Starting Production Container Engine      "
echo "=========================================================="

# Ensure upload directories exist and have proper permissions
mkdir -p /var/www/html/uploads/gallery /var/www/html/uploads/trainers /var/www/html/backups
chown -R www-data:www-data /var/www/html/uploads /var/www/html/backups
chmod -R 775 /var/www/html/uploads /var/www/html/backups

# If PORT is specified by container host (e.g. Render / Cloud Run / Railway), adjust Apache ports
if [ -n "$PORT" ]; then
    echo "Configuring Apache to listen on port ${PORT}..."
    sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
    sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf
fi

# Run Database Schema Migration if DB_HOST or DATABASE_URL is configured
if [ -n "$DB_HOST" ] || [ -n "$DATABASE_URL" ] || [ -n "$MYSQL_URL" ]; then
    echo "Executing database migration telemetry..."
    php /var/www/html/scripts/migrate.php || echo "[WARNING] Database migration encountered an issue. Container proceeding..."
fi

echo "Handing off execution to Apache web server..."
exec "$@"
