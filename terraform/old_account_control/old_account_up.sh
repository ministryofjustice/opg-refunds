#!/bin/bash

aws-vault exec identity -- terraform init
echo "following will prompt for stack name to target"
aws-vault exec identity -- terraform apply -auto-approve -var stack=preprod -var state_up=true

# start
aws-vault exec refunds-dev -- aws rds start-db-instance --db-instance-identifier applications-preprod && \
aws-vault exec refunds-dev -- aws rds start-db-instance --db-instance-identifier caseworker-preprod
