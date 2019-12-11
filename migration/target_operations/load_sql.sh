#!/usr/bin/env bash

PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id $ENV_NAME/opg_refunds_db_cases_migration_password | jq -r .'SecretString') psql -v ON_ERROR_STOP=1 -h $CASES_DB_ENDPOINT -U cases_migration cases < /mnt/sql/cases.sql
