#!/usr/bin/env bash

alias l="ls -al"
alias gco="git checkout"
alias ga="git add"
alias gc="git commit"
alias gst="git status"
alias ..="cd .."
alias concases="PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id $ENV_NAME/opg_refunds_db_cases_migration_password | jq -r .'SecretString') psql -h $CASES_DB_ENDPOINT -U cases_migration cases"
alias concases_root="PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id $ENV_NAME/postgres_password | jq -r .'SecretString') psql -h $CASES_DB_ENDPOINT -U root cases"
