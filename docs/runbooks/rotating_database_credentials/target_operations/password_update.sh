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
  echo "ENVIRONMENT: ${ENVIRONMENT}"
  echo "DB_NAME: ${DB_NAME}"
  echo "DB_CREDENTIAL: ${DB_CREDENTIAL}"
  echo "DB_USERNAME: ${DB_USERNAME}"
  echo "ACCOUNT: ${ACCOUNT}"
  echo "PGHOST: ${PGHOST}"
  echo "PUT_SECRET_OPTS: ${PUT_SECRET_OPTS}"
}

function setup() {
  case "${ENVIRONMENT}" in
      'preproduction')
        ACCOUNT="${ENVIRONMENT}"
        ;;
      'production')
        ACCOUNT="${ENVIRONMENT}"
        ;;
      *)
        ACCOUNT="development"
        ;;
  esac
  if [ -z $APPLICATIONS_HOST ] || [ -z $CASEWORKER_HOST ]
  then
    echo "PGHOST cannot be set"
    exit
  else
    case $DB_NAME in
      'applications')
        PGHOST=${APPLICATIONS_HOST}
        ;;
      *)
        PGHOST=${CASEWORKER_HOST}
        ;;
    esac
  fi
}

function generate_new_password() {
  openssl rand -base64 33
}

function update_secret() {
  echo "putting new password..."
  aws secretsmanager put-secret-value \
  --secret-id ${ACCOUNT}/opg_refunds_db_${DB_CREDENTIAL}_password \
  --secret-string "$(generate_new_password)" ${PUT_SECRET_OPTS}
}

function db_passwords_update() {
  echo "updating password in db..."
  export DB_PASSWORD=$(aws secretsmanager get-secret-value --secret-id ${ACCOUNT}/opg_refunds_db_${DB_CREDENTIAL}_password | jq -r .'SecretString')
  export DB_USERNAME=${DB_USERNAME}
  export ROOT_PASSWORD=$(aws secretsmanager get-secret-value --secret-id ${ACCOUNT}/postgres_password | jq -r .'SecretString')
  PGPASSWORD=${ROOT_PASSWORD} psql -v ON_ERROR_STOP=1 -d ${DB_NAME} < sql_scripts/password_update.sql
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
          ENVIRONMENT="$2"
          shift
          shift
          ;;
          -d|--database)
          DB_NAME="$2"
          shift
          shift
          ;;
          -c|--credential)
          DB_CREDENTIAL="$2"
          shift
          shift
          ;;
          -u|--username)
          DB_USERNAME="$2"
          shift
          ;;
          -t|--test)
          TEST=True
          shift
          ;;
      esac
  done
}

function start() {
  if [ $TEST = True ]
  then
    test_run
  else
    run
  fi
}

TEST=False
parse_args $@
start
