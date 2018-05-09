\set DATABASE_NAME `echo ${OPG_REFUNDS_DB_MERIS_NAME}`
\set DATABASE_FULL_USERNAME `echo ${OPG_REFUNDS_DB_MERIS_FULL_USERNAME}`
\set DATABASE_FULL_PASSWORD `echo ${OPG_REFUNDS_DB_MERIS_FULL_PASSWORD}`
\set DATABASE_MIGRATION_USERNAME `echo ${OPG_REFUNDS_DB_MERIS_MIGRATION_USERNAME}`
\set DATABASE_MIGRATION_PASSWORD `echo ${OPG_REFUNDS_DB_MERIS_MIGRATION_PASSWORD}`

CREATE DATABASE :DATABASE_NAME;

\c :DATABASE_NAME

BEGIN;

REVOKE ALL ON ALL SEQUENCES IN SCHEMA public FROM public;
REVOKE ALL ON ALL TABLES IN SCHEMA public FROM public;
REVOKE ALL ON SCHEMA public FROM public;

CREATE USER :DATABASE_FULL_USERNAME WITH UNENCRYPTED PASSWORD :'DATABASE_FULL_PASSWORD';

GRANT CONNECT ON DATABASE :DATABASE_NAME TO :DATABASE_FULL_USERNAME;
GRANT USAGE ON SCHEMA public TO :DATABASE_FULL_USERNAME;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO :DATABASE_FULL_USERNAME;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO :DATABASE_FULL_USERNAME;

CREATE USER :DATABASE_MIGRATION_USERNAME WITH UNENCRYPTED PASSWORD :'DATABASE_MIGRATION_PASSWORD';
GRANT ALL PRIVILEGES ON DATABASE :DATABASE_NAME TO :DATABASE_MIGRATION_USERNAME;
GRANT ALL PRIVILEGES ON SCHEMA public TO :DATABASE_MIGRATION_USERNAME;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO :DATABASE_MIGRATION_USERNAME;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO :DATABASE_MIGRATION_USERNAME;

COMMIT;
