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
export OPG_REFUNDS_DB_MIGRATION_PASSWORD=$(aws secretsmanager get-secret-value --secret-id $ENV_NAME/opg_refunds_db_migration_password | jq -r .'SecretString')

PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id $ENV_NAME/postgres_password | jq -r .'SecretString') psql -v ON_ERROR_STOP=1 -h $CASES_DB_ENDPOINT -U root cases < /mnt/opg-refunds/migration/target_operations/create_migration_user.sh.sql
