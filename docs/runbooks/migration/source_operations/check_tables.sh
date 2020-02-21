#!/usr/bin/env bash

mkdir -p /mnt/sql
# Find DB PASSWORDs and enter below
# grep database_master_password opg-refund-deploy/ansible/production/env_vars.yml
APP_DB_PASS=
CASES_DB_PASS=
# grep caseworker_logical_dbs -a6 opg-refund-deploy/ansible/production/env_vars.yml | grep sirius -a2 -b1| grep full_password
SIRIUS_DB_PASS=
CASEWORKER_DB_ENDPOINT="caseworker.production.internal"
APPLICATIONS_DB_ENDPOINT="applications.production.internal"

SCRIPTS_PATH="/mnt/opg-refunds/migration/sql_scripts"
DATA_PATH="/mnt/sql"

CASES_ROOT_OPTS="-h ${CASEWORKER_DB_ENDPOINT} -U cases_full"
CASES_MIGRATION_OPTS="-h ${CASEWORKER_DB_ENDPOINT} -U cases_full"
SIRIUS_MIGRATION_OPTS="-h ${CASEWORKER_DB_ENDPOINT} -U sirius_full"
APPLICATIONS_MIGRATION_OPTS="-h ${APPLICATIONS_DB_ENDPOINT} -U refunds_master_full"
PSQL="psql -v ON_ERROR_STOP=1"

# check cases
PGPASSWORD=${CASES_DB_PASS} ${PSQL} ${CASES_ROOT_OPTS} cases < ${SCRIPTS_PATH}/cases_check_tables_root.sql
PGPASSWORD=${CASES_DB_PASS} ${PSQL} ${CASES_MIGRATION_OPTS} cases < ${SCRIPTS_PATH}/cases_check_tables_cm.sql
PGPASSWORD=${SIRIUS_DB_PASS} ${PSQL} ${SIRIUS_MIGRATION_OPTS} sirius < ${SCRIPTS_PATH}/sirius_check_tables_sm.sql
PGPASSWORD=${APP_DB_PASS} ${PSQL} ${APPLICATIONS_MIGRATION_OPTS} applications < ${SCRIPTS_PATH}/applications_check_tables_am.sql
