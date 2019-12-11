#!/usr/bin/env bash

export ENV_NAME=preproduction
export AWS_DEFAULT_REGION=eu-west-1
export OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME=$(aws rds describe-db-clusters --db-cluster-identifier applications-$ENV_NAME | jq -r .'DBClusters'[0].'Endpoint')
export OPG_REFUNDS_DB_APPLICATIONS_PORT=5432
export CASES_DB_ENDPOINT=$(aws rds describe-db-clusters --db-cluster-identifier caseworker-$ENV_NAME | jq -r .'DBClusters'[0].'Endpoint')
export OPG_REFUNDS_DB_FINANCE_HOSTNAME=$CASES_DB_ENDPOINT
export OPG_REFUNDS_DB_FINANCE_PORT=5432
export OPG_REFUNDS_DB_SIRIUS_HOSTNAME=$CASES_DB_ENDPOINT
export OPG_REFUNDS_DB_SIRIUS_PORT=5432
export OPG_REFUNDS_DB_MERIS_HOSTNAME=$CASES_DB_ENDPOINT
export OPG_REFUNDS_DB_MERIS_PORT=5432
export POSTGRES_USER=root
