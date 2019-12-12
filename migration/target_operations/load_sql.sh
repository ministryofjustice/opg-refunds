#!/usr/bin/env bash

export ENV_NAME=preproduction
export AWS_DEFAULT_REGION=eu-west-1
export CASES_DB_ENDPOINT=$(aws rds describe-db-clusters --db-cluster-identifier caseworker-$ENV_NAME | jq -r .'DBClusters'[0].'Endpoint')
export OPG_REFUNDS_DB_FINANCE_HOSTNAME=$CASES_DB_ENDPOINT
export OPG_REFUNDS_DB_FINANCE_PORT=5432
export OPG_REFUNDS_DB_SIRIUS_HOSTNAME=$CASES_DB_ENDPOINT
export OPG_REFUNDS_DB_SIRIUS_PORT=5432
export OPG_REFUNDS_DB_MERIS_HOSTNAME=$CASES_DB_ENDPOINT
export OPG_REFUNDS_DB_MERIS_PORT=5432

# truncate
PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id $ENV_NAME/opg_refunds_db_cases_migration_password | jq -r .'SecretString') psql -v ON_ERROR_STOP=1 -h $CASES_DB_ENDPOINT -U cases_migration cases < /mnt/opg-refunds/migration/target_operations/claim_truncate_tables_drop_index_cm.sql
PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id $ENV_NAME/postgres_password | jq -r .'SecretString') psql -v ON_ERROR_STOP=1 -h $CASES_DB_ENDPOINT -U root cases < /mnt/opg-refunds/migration/target_operations/claim_truncate_tables_drop_index_root.sql

# load
PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id $ENV_NAME/postgres_password | jq -r .'SecretString') pg_restore --data-only --exit-on-error --table=finance --table=meris --table=sirius -h $CASES_DB_ENDPOINT -U root --dbname=cases --verbose /mnt/sql/cases.tar

PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id $ENV_NAME/opg_refunds_db_cases_migration_password | jq -r .'SecretString') pg_restore --data-only --exit-on-error --table=claim --table=doctrine_migration_versions --table=duplicate_claims --table=note --table=payment --table=poa --table=report --tabscle=user --table=verification -h $CASES_DB_ENDPOINT -U cases_migration --dbname=cases /mnt/sql/cases.tar

# check
PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id $ENV_NAME/postgres_password | jq -r .'SecretString') psql -v ON_ERROR_STOP=1 -h $CASES_DB_ENDPOINT -U root cases < /mnt/opg-refunds/migration/target_operations/check_tables_root.sql

PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id $ENV_NAME/opg_refunds_db_cases_migration_password | jq -r .'SecretString') psql -v ON_ERROR_STOP=1 -h $CASES_DB_ENDPOINT -U cases_migration cases < /mnt/opg-refunds/migration/target_operations/check_tables_cm.sql
