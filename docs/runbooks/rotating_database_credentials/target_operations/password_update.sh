#!/usr/bin/env bash

function run() {
  setup
  PUT_SECRET_OPTS=''
  update_secret
  db_passwords_update
  redeploy_ecs_services
}

function test_run() {
  setup
  PUT_SECRET_OPTS='--generate-cli-skeleton'
  print_variables
  update_secret
  echo "updating password in db..."
  echo "redeploying services..."
}

function print_variables() {
  echo "ENVIRONMENT: $ENVIRONMENT"
  echo "DB_NAME: $DB_NAME"
  echo "DB_CREDENTIAL: $DB_CREDENTIAL"
  echo "DB_USERNAME: $DB_USERNAME"
  echo "ACCOUNT: $ACCOUNT"
  echo "PGHOST: $PGHOST"
  echo "PUT_SECRET_OPTS: $PUT_SECRET_OPTS"
}

function setup() {
  case "${ENVIRONMENT}" in
    'preproduction')
      ACCOUNT="${1:?}"
      ;;
    'production')
      ACCOUNT="${1:?}"
      ;;
    *)
      ACCOUNT="development"
      ;;
  esac

  case $DB_NAME in
    'applications')
      PGHOST=${APPLICATIONS_HOST}
      ;;
    *)
      PGHOST=${CASEWORKER_HOST}
      ;;
  esac
}

function generate_new_password() {
  openssl rand -base64 33
}

function update_secret() {
  echo "putting new password..."
  aws secretsmanager put-secret-value \
  --secret-id ${ACCOUNT}/opg_refunds_db_${DB_CREDENTIAL}_password \
  --secret-string $(generate_new_password) $PUT_SECRET_OPTS
}

function db_passwords_update() {
  echo "updating password in db..."
  DB_PASSWORD=$(aws secretsmanager get-secret-value --secret-id ${ACCOUNT}/opg_refunds_db_${DB_CREDENTIAL}_password | jq -r .'SecretString')
  psql -v ON_ERROR_STOP=1 $DB_NAME < ../sql_scripts/password_update.sql
}

function redeploy_ecs_services() {
  echo "redeploying services..."
  aws ecs update-service --cluster ${ENVIRONMENT}-lpa-refunds --force-new-deployment --service public-front
  aws ecs update-service --cluster ${ENVIRONMENT}-lpa-refunds --force-new-deployment --service ingestion
  aws ecs update-service --cluster ${ENVIRONMENT}-lpa-refunds --force-new-deployment --service caseworker_api
  aws ecs update-service --cluster ${ENVIRONMENT}-lpa-refunds --force-new-deployment --service caseworker-front
}

function parse_args() {
for arg in "$@"
do
    case $arg in
        -e|--environment)
        ENVIRONMENT="${arg#*=}"
        shift # Remove --cache= from processing
        ;;
        -d|--database)
        DB_NAME="${arg#*=}"
        shift # Remove --cache= from processing
        ;;
        -c|--credential)
        DB_CREDENTIAL="${arg#*=}"
        shift # Remove --cache= from processing
        ;;
        -u|--username)
        DB_USERNAME="${arg#*=}"
        shift # Remove --cache= from processing
        ;;
    esac
done

parse_args $@

echo "ENVIRONMENT: $ENVIRONMENT"
echo "DB_NAME: $DB_NAME"
echo "DB_CREDENTIAL: $DB_CREDENTIAL"
echo "DB_USERNAME: $DB_USERNAME"
echo "ACCOUNT: $ACCOUNT"
echo "PGHOST: $PGHOST"
echo "PUT_SECRET_OPTS: $PUT_SECRET_OPTS"

# while getopts e:d:c:u: option
# do
# case "${option}"
# in
# -e|--environment) ENVIRONMENT=${OPTARG};;
# -d|--database) DB_NAME=${OPTARG};;
# -c|--credential) DB_CREDENTIAL=${OPTARG};;
# -u|--username) DB_USERNAME=${OPTARG};;
# # t) TEST=${OPTARG};;
# # h) echo "USAGE: source password_update.sh -e <ENVIRONMENT> -d <DATABASE> -c <CREDENTIAL_TO_BE_UPDATED> -u <USERNAME> -t true"
# esac
# done



# if [ $TEST == 'true' ]
# then
test_run
# else
# run
# fi
