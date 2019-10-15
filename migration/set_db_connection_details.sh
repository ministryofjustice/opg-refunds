# export PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id development/postgres_password | jq -r .'SecretString')
# export OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME=$(aws rds describe-db-clusters --db-cluster-identifier applications-$ENV_NAME | jq -r .'DBClusters'[0].'Endpoint')

# working with caseworker db
export ENV_NAME=$(echo "$TF_WORKSPACE" | awk '{print tolower($0)}')
export OPG_REFUNDS_DB_CASEWORKER_HOSTNAME=$(aws rds describe-db-clusters --db-cluster-identifier caseworker-$ENV_NAME | jq -r .'DBClusters'[0].'Endpoint')
export OPG_REFUNDS_DB_CASES_FULL_USERNAME="cases_full"
export POSTGRES_USER=$OPG_REFUNDS_DB_CASES_FULL_USERNAME
export PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id development/opg_refunds_db_cases_full_password | jq -r .'SecretString')

psql -h $OPG_REFUNDS_DB_CASEWORKER_HOSTNAME -U $POSTGRES_USER cases  