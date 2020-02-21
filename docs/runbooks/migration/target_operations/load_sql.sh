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
# truncate
PGPASSWORD=${CASES_MIGRATION_PASSWORD} ${PSQL} ${CASES_MIGRATION_OPTS} cases < ${SCRIPTS_PATH}/cases_truncate_tables_cm.sql
PGPASSWORD=${ROOT_PASSWORD} ${PSQL} ${CASES_ROOT_OPTS} cases < ${SCRIPTS_PATH}/cases_truncate_tables_root.sql
PGPASSWORD=${SIRIUS_MIGRATION_PASSWORD} ${PSQL} ${SIRIUS_MIGRATION_OPTS} sirius < ${SCRIPTS_PATH}/sirius_truncate_tables_sm.sql
PGPASSWORD=${APPLICATIONS_MIGRATION_PASSWORD} ${PSQL} ${APPLICATIONS_MIGRATION_OPTS} applications < ${SCRIPTS_PATH}/applications_truncate_tables_am.sql

# load cases
PGPASSWORD=${ROOT_PASSWORD} pg_restore ${CASES_ROOT_OPTS} \
    --dbname=cases \
    --data-only \
    --table=finance \
    --table=meris \
    --table=sirius \
    --verbose \
    --exit-on-error \
    ${DATA_PATH}/cases.tar

PGPASSWORD=${CASES_MIGRATION_PASSWORD} pg_restore ${CASES_MIGRATION_OPTS}  \
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

PGPASSWORD=${SIRIUS_MIGRATION_PASSWORD} pg_restore ${SIRIUS_MIGRATION_OPTS}  \
    --dbname=sirius \
    --data-only \
    --table=doctrine_migration_versions \
    --table=poa \
    --verbose \
    --exit-on-error \
    ${DATA_PATH}/sirius.tar

PGPASSWORD=${APPLICATIONS_MIGRATION_PASSWORD} pg_restore ${APPLICATIONS_MIGRATION_OPTS}  \
    --dbname=applications \
    --data-only \
    --table=application \
    --verbose \
    --exit-on-error \
    ${DATA_PATH}/applications.tar

# check cases
PGPASSWORD=${ROOT_PASSWORD} ${PSQL} ${CASES_ROOT_OPTS} cases < ${SCRIPTS_PATH}/cases_check_tables_root.sql
PGPASSWORD=${CASES_MIGRATION_PASSWORD} ${PSQL} ${CASES_MIGRATION_OPTS} cases < ${SCRIPTS_PATH}/cases_check_tables_cm.sql
PGPASSWORD=${SIRIUS_MIGRATION_PASSWORD} ${PSQL} ${SIRIUS_MIGRATION_OPTS} sirius < ${SCRIPTS_PATH}/sirius_check_tables_sm.sql
PGPASSWORD=${APPLICATIONS_MIGRATION_PASSWORD} ${PSQL} ${APPLICATIONS_MIGRATION_OPTS} applications < ${SCRIPTS_PATH}/applications_check_tables_am.sql
