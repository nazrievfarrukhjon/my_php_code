DO
$do$
    BEGIN
        IF NOT EXISTS (SELECT FROM pg_roles WHERE rolname = 'replicator') THEN
            CREATE USER replicator REPLICATION LOGIN PASSWORD 'replicator_pass';
        END IF;
    END
$do$;

DO
$do$
    BEGIN
        IF NOT EXISTS (SELECT FROM pg_database WHERE datname = 'my_php_code_db') THEN
            CREATE DATABASE my_php_code_db;
        END IF;
    END
$do$;

\connect my_php_code_db
CREATE EXTENSION IF NOT EXISTS pg_trgm;
