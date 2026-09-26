#!/bin/sh
set -eu
STAMP=$(date -u +%Y%m%dT%H%M%SZ)
mkdir -p /backups
mysqldump --single-transaction --quick -h "$DB_HOST" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" | gzip > "/backups/database-$STAMP.sql.gz"
tar -czf "/backups/uploads-$STAMP.tar.gz" -C /var/www/html/storage/app .
find /backups -type f -mtime +"${BACKUP_RETENTION_DAYS:-14}" -delete
