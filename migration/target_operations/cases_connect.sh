#!/usr/bin/env bash


if [[ -z "$ENV_NAME" ]]; then
  echo "ENV_NAME not set!"
  exit 1
else
  echo "environment set as $ENV_NAME"
fi


CASES_DB_ENDPOINT=$(aws rds describe-db-clusters --db-cluster-identifier caseworker-${ENV_NAME} | jq -r .'DBClusters'[0].'Endpoint')

SCRIPTS_PATH="/mnt/opg-refunds/migration/target_operations"
DATA_PATH="/mnt/sql"

ROOT_PASSWORD=$(aws secretsmanager get-secret-value --secret-id ${ENV_NAME}/postgres_password | jq -r .'SecretString') 
CASES_MIGRATION_PASSWORD=$(aws secretsmanager get-secret-value --secret-id ${ENV_NAME}/opg_refunds_db_cases_migration_password | jq -r .'SecretString')

CASES_ROOT_OPTS="-h ${CASES_DB_ENDPOINT} -U root"
CASES_MIGRATION_OPTS="-h ${CASES_DB_ENDPOINT} -U cases_migration"

PSQL="psql -v ON_ERROR_STOP=1"

PGPASSWORD=${ROOT_PASSWORD} ${PSQL} ${ROOT_OPTS} postgres
