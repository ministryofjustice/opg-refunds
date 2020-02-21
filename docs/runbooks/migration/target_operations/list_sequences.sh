#!/usr/bin/env bash


if [[ -z "$ENV_NAME" ]]; then
  echo "ENV_NAME not set!"
  exit 1
else
  echo "environment set as $ENV_NAME"
fi

CASEWORKER_DB_ENDPOINT=$(aws rds describe-db-clusters --db-cluster-identifier caseworker-${ENV_NAME} | jq -r .'DBClusters'[0].'Endpoint')
APPLICATIONS_DB_ENDPOINT=$(aws rds describe-db-clusters --db-cluster-identifier applications-${ENV_NAME} | jq -r .'DBClusters'[0].'Endpoint')

SCRIPTS_PATH="/mnt/opg-refunds/migration/sql_scripts"
DATA_PATH="/mnt/sql"

ROOT_PASSWORD=$(aws secretsmanager get-secret-value --secret-id ${ENV_NAME}/postgres_password | jq -r .'SecretString')
CASES_MIGRATION_PASSWORD=$(aws secretsmanager get-secret-value --secret-id ${ENV_NAME}/opg_refunds_db_cases_migration_password | jq -r .'SecretString')
SIRIUS_MIGRATION_PASSWORD=$(aws secretsmanager get-secret-value --secret-id ${ENV_NAME}/opg_refunds_db_sirius_migration_password | jq -r .'SecretString')
APPLICATIONS_MIGRATION_PASSWORD=$(aws secretsmanager get-secret-value --secret-id ${ENV_NAME}/opg_refunds_db_applications_migration_password | jq -r .'SecretString')

CASES_ROOT_OPTS="-h ${CASEWORKER_DB_ENDPOINT} -U root"
CASES_MIGRATION_OPTS="-h ${CASEWORKER_DB_ENDPOINT} -U cases_migration"
SIRIUS_MIGRATION_OPTS="-h ${CASEWORKER_DB_ENDPOINT} -U sirius_migration"
APPLICATIONS_MIGRATION_OPTS="-h ${APPLICATIONS_DB_ENDPOINT} -U applications_migration"

PSQL="psql -v ON_ERROR_STOP=1"

# check cases
PGPASSWORD=${ROOT_PASSWORD} ${PSQL} ${CASES_ROOT_OPTS} cases < ${SCRIPTS_PATH}/cases_list_sequences_root.sql
PGPASSWORD=${CASES_MIGRATION_PASSWORD} ${PSQL} ${CASES_MIGRATION_OPTS} cases < ${SCRIPTS_PATH}/cases_list_sequences_cm.sql
PGPASSWORD=${SIRIUS_MIGRATION_PASSWORD} ${PSQL} ${SIRIUS_MIGRATION_OPTS} sirius < ${SCRIPTS_PATH}/sirius_list_sequences_sm.sql
PGPASSWORD=${APPLICATIONS_MIGRATION_PASSWORD} ${PSQL} ${APPLICATIONS_MIGRATION_OPTS} applications < ${SCRIPTS_PATH}/applications_list_sequences_am.sql
