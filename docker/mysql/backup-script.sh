#!/bin/sh
echo "--- START SKRYPTU ---"
until mysql -h"$MYSQL_HOST" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "SELECT 1" >/dev/null 2>&1; do
    echo "Czekam na MySQL..."
    sleep 2
done
while true; do
    TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
    echo "Backup: $TIMESTAMP"
    mysqldump -h"$MYSQL_HOST" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" --all-databases --no-tablespaces > "/backups/backup_${TIMESTAMP}.sql"
    sleep 3600
done
