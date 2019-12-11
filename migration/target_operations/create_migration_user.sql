\set DATABASE_FULL_PASSWORD `echo ${OPG_REFUNDS_DB_MIGRATION_PASSWORD}`

DROP USER IF EXISTS migration;
CREATE USER migration WITH PASSWORD :'DATABASE_FULL_PASSWORD';

GRANT ALL PRIVILEGES ON DATABASE cases TO migration;
GRANT ALL PRIVILEGES ON SCHEMA public TO migration;
