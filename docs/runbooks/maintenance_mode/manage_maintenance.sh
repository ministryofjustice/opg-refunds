#!/usr/bin/env bash

function configure_terraform() {
  export TF_VAR_default_role=${C9_USER}
  export TF_VAR_old_account_default_role=${C9_USER}
  export TF_CLI_ARGS_init="-backend-config=role_arn=arn:aws:iam::311462405659:role/${C9_USER} -upgrade=true  -reconfigure"
}

function enable_maintenance() {
  aws ssm put-parameter --name "${ENVIRONMENT}_enable_maintenance" --type "String" --value "true" --overwrite
  terraform workspace select ${ENVIRONMENT}
  terraform apply --target aws_lb_listener_rule.public_front_maintenance --auto-approve
}

function disable_maintenance() {
  aws ssm put-parameter --name "${ENVIRONMENT}_enable_maintenance" --type "String" --value "false" --overwrite
  terraform workspace select ${ENVIRONMENT}
  terraform apply --target aws_lb_listener_rule.public_front_maintenance --auto-approve
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
          -m|--maintenace_mode)
          MAINTENACE_MODE=True
          shift
          ;;
      esac
  done
}

function start() {
  if [ $MAINTENACE_MODE = True ]
  then
    enable_maintenance
  else
    disable_maintenance
  fi
}

MAINTENACE_MODE=False
parse_args $@
configure_terraform
start
