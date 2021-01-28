#!/usr/bin/env bash

function get_alb_rule_arn() {
  MM_DNS_PREFIX="${ENVIRONMENT}."
  MM_ALB_ARN=$(aws elbv2 describe-load-balancers --names  "${ENVIRONMENT}-public-front" | jq -r .[][]."LoadBalancerArn")
  MM_LISTENER_ARN=$(aws elbv2 describe-listeners --load-balancer-arn ${MM_ALB_ARN} | jq -r '.[][]  | select(.Protocol == "HTTPS") | .ListenerArn')
  MM_RULE_ARN=$(aws elbv2 describe-rules --listener-arn ${MM_LISTENER_ARN} | jq -r '.[][]  | select(.Priority == "2") | .RuleArn')
  SHUTDOWN_RULE_ARN=$(aws elbv2 describe-rules --listener-arn ${MM_LISTENER_ARN} | jq -r '.[][]  | select(.Priority == "1") | .RuleArn')

  if [ $ENVIRONMENT = "production" ]
  then
    MM_DNS_PREFIX=""
  fi
}

function enable_maintenance() {
  aws ssm put-parameter --name "${ENVIRONMENT}_enable_maintenance" --type "String" --value "true" --overwrite
  aws elbv2 modify-rule \
  --rule-arn $MM_RULE_ARN \
  --conditions Field=host-header,Values="${MM_DNS_PREFIX}claim-power-of-attorney-refund.service.gov.uk"
}

function disable_maintenance() {
  aws ssm put-parameter --name "${ENVIRONMENT}_enable_maintenance" --type "String" --value "false" --overwrite
  aws elbv2 modify-rule \
  --rule-arn $MM_RULE_ARN \
  --conditions Field=path-pattern,Values='/maintenance'
}

function enable_shutdown() {
  aws ssm put-parameter --name "${ENVIRONMENT}_enable_shutdown" --type "String" --value "true" --overwrite
  aws elbv2 modify-rule \
  --rule-arn $SHUTDOWN_RULE_ARN \
  --conditions Field=host-header,Values="${MM_DNS_PREFIX}claim-power-of-attorney-refund.service.gov.uk"
}

function disable_shutdown() {
  aws ssm put-parameter --name "${ENVIRONMENT}_enable_shutdown" --type "String" --value "false" --overwrite
  aws elbv2 modify-rule \
  --rule-arn $SHUTDOWN_RULE_ARN \
  --conditions Field=path-pattern,Values='/shutdown'
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
          -m|--maintenance_mode)
          MAINTENANCE_MODE=True
          shift
          ;;
          -d|--disable_maintenance_mode)
          MAINTENANCE_MODE=False
          shift
          ;;
          -s|--shutdown_enabled)
          SHUTDOWN_MODE=True
          shift
          ;;
          -u|--shutdown_disabled)
          SHUTDOWN_MODE=False
          shift
          ;;
      esac
  done
}

function start() {
  if [ $MAINTENANCE_MODE = "True" ]
  then
    enable_maintenance
  else
    disable_maintenance
  fi

  if [ $SHUTDOWN_MODE = "True" ]
  then
    enable_shutdown
  else
    disable_shutdown
  fi
}

MAINTENANCE_MODE=False
SHUTDOWN_MODE=False
parse_args $@
get_alb_rule_arn
start
