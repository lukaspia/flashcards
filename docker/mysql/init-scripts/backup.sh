#!/bin/bash

# Funkcja wykonujaca backup
backup() {
    local BACKUP_DIR="/var/lib/mysql-backups"
    local TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
    mkdir -p "$BACKUP_DIR"
    echo "Starting backup to $BACKUP_DIR/backup_${TIMESTAMP}.sql"
    mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" --all-databases > "$BACKUP_DIR/backup_${TIMESTAMP}.sql"
    # Upewnij sie, ze backup sie powiodl
    if [ $? -eq 0 ]; then
        echo "Backup completed successfully: $BACKUP_DIR/backup_${TIMESTAMP}.sql"
    else
        echo "Backup failed!" >&2
        exit 1
    fi
}

# Zarejestruj funkcje trap, ktora wywola backup przed zakonczeniem kontenera
trap 'backup; exit 0' SIGTERM

# Uruchom oryginalne polecenie startowe MySQL w tle
"$@" &

# Poczekaj na zakonczenie procesu MySQL
wait $!
