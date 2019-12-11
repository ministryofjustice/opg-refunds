#!/usr/bin/env bash

export ENV_NAME=preproduction
export AWS_DEFAULT_REGION=eu-west-1
export OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME=$(aws rds describe-db-clusters --db-cluster-identifier applications-$ENV_NAME | jq -r .'DBClusters'[0].'Endpoint')
export OPG_REFUNDS_DB_APPLICATIONS_PORT=5432
export CASES_DB_ENDPOINT=$(aws rds describe-db-clusters --db-cluster-identifier caseworker-$ENV_NAME | jq -r .'DBClusters'[0].'Endpoint')

mkdir -p /mnt/sql

PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id $ENV_NAME/opg_refunds_db_applications_full_password | jq -r .'SecretString') pg_dump --data-only -h $OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME -U applications_full --file=/mnt/sql/target_schema_applications.sql applications

PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id $ENV_NAME/opg_refunds_db_cases_full_password | jq -r .'SecretString') pg_dump --data-only -h $CASES_DB_ENDPOINT -U cases_full --file=/mnt/sql/target_schema_cases.sql cases

PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id $ENV_NAME/postgres_password | jq -r .'SecretString')  pg_dump --data-only -h $CASES_DB_ENDPOINT -U root --file=/mnt/sql/target_schema_caseworker.sql caseworker

PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id $ENV_NAME/opg_refunds_db_finance_full_password | jq -r .'SecretString') pg_dump --data-only -h $CASES_DB_ENDPOINT -U finance_full --file=/mnt/sql/target_schema_finance.sql finance

PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id $ENV_NAME/opg_refunds_db_meris_full_password | jq -r .'SecretString') pg_dump --data-only -h $CASES_DB_ENDPOINT -U meris_full --file=/mnt/sql/target_schema_meris.sql meris

PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id $ENV_NAME/opg_refunds_db_sirius_full_password | jq -r .'SecretString') pg_dump --data-only -h $CASES_DB_ENDPOINT -U sirius_full --file=/mnt/sql/target_schema_sirius.sql sirius

aws s3 cp /mnt/sql/target_schema_applications.sql s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp /mnt/sql/target_schema_cases.sql s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp /mnt/sql/target_schema_caseworker.sql s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp /mnt/sql/target_schema_finance.sql s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp /mnt/sql/target_schema_meris.sql s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp /mnt/sql/target_schema_sirius.sql s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
