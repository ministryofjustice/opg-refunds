#!/usr/bin/env bash

function get_alb_rule_arn() {
  MM_DNS_PREFIX="${ENVIRONMENT}."
  MM_ALB_ARN=$(aws elbv2 describe-load-balancers --names  "${ENVIRONMENT}-public-front" | jq -r .[][]."LoadBalancerArn")
  MM_LISTENER_ARN=$(aws elbv2 describe-listeners --load-balancer-arn ${MM_ALB_ARN} | jq -r '.[][]  | select(.Protocol == "HTTPS") | .ListenerArn')
  MM_RULE_ARN=$(aws elbv2 describe-rules --listener-arn ${MM_LISTENER_ARN} | jq -r '.[][]  | select(.Priority == "1") | .RuleArn')
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
          -d|--disable_maintenace_mode)
          MAINTENACE_MODE=False
          shift
          ;;
      esac
  done
}

function start() {
  if [ $MAINTENACE_MODE = "True" ]
  then
    enable_maintenance
  else
    disable_maintenance
  fi
}

MAINTENACE_MODE=False
parse_args $@
echo $ENVIRONMENT
echo $MAINTENACE_MODE
get_alb_rule_arn
start
