#!/usr/bin/env bash

if [[ -z "${ENV_NAME}" ]]; then
  "environment set as $ENV_VAR"
else
  "ENV_NAME not set!"
  exit 1
fi

CASES_DB_ENDPOINT=$(aws rds describe-db-clusters --db-cluster-identifier caseworker-${ENV_NAME} | jq -r .'DBClusters'[0].'Endpoint')
APPLICATIONS_DB_ENDPOINT=$(aws rds describe-db-clusters --db-cluster-identifier applications-$ENV_NAME | jq -r .'DBClusters'[0].'Endpoint')
SCRIPTS_PATH="/mnt/opg-refunds/migration/target_operations"
DATA_PATH="/mnt/sql"
ROOT_PASSWORD=$(aws secretsmanager get-secret-value --secret-id ${ENV_NAME}/postgres_password | jq -r .'SecretString') 
CASES_MIGRATION_PASSWORD=$(aws secretsmanager get-secret-value --secret-id ${ENV_NAME}/opg_refunds_db_cases_migration_password | jq -r .'SecretString')

ROOT_OPTS="-h ${CASES_DB_ENDPOINT} -U root -W ${ROOT_PASSWORD}"
CASES_MIGRATION_OPTS="-h ${CASES_DB_ENDPOINT} -U cases_migration -W ${CASES_MIGRATION_PASSWORD}"

PSQL="psql -v ON_ERROR_STOP=1"
# truncate cases
${PSQL} ${CASES_MIGRATION_OPTS} cases < ${SCRIPTS_PATH}/cases_truncate_tables_drop_index_cm.sql
${PSQL} ${ROOT_OPTS} cases < ${SCRIPTS_PATH}/cases_truncate_tables_drop_index_root.sql

# load cases
pg_restore ${ROOT_OPTS} \
    --dbname=cases \
    --data-only \
    --table=finance \
    --table=meris \
    --table=sirius \
    --verbose \
    --exit-on-error \
    ${DATA_PATH}/cases.tar

pg_restore ${CASES_MIGRATION_OPTS}  \
    --dbname=cases \
    --data-only \
    --table=claim \
    --table=doctrine_migration_versions \
    --table=duplicate_claims \
    --table=note \
    --table=payment \
    --table=poa \
    --table=report \
    --table=user \
    --table=verification \
    --verbose \
    --exit-on-error \
    ${DATA_PATH}/cases.tar

# check cases
${PSQL} ${ROOT_OPTS} cases < ${SCRIPTS_PATH}/check_tables_root.sql
${PSQL} ${CASES_MIGRATION_OPTS} cases < ${SCRIPTS_PATH}/check_tables_cm.sql
