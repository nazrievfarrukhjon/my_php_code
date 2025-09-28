#!/bin/bash
set -e

# Clear data directory (optional if volume is fresh)
rm -rf /var/lib/postgresql/data/*

# Wait for primary to be ready
until PGPASSWORD=replicator_pass psql -h pg_primary -U replicator -d my_php_code_db -c '\q' 2>/dev/null; do
  echo "Waiting for primary to be ready..."
  sleep 2
done

# Base backup from primary
PGPASSWORD=replicator_pass pg_basebackup -h pg_primary -D /var/lib/postgresql/data -U replicator -v -P -R

# Start PostgreSQL
exec postgres -c config_file=/etc/postgresql/postgresql.conf
