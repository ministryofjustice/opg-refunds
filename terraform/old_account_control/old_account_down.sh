#!/bin/bash

aws-vault exec identity -- terraform init
echo "following will prompt for stack name to target"
aws-vault exec identity -- terraform apply -auto-approve -var stack=preprod -var state_up=false

read -p "Press enter to stop DB instances"

# stop
aws-vault exec refunds-dev -- aws rds stop-db-instance --db-instance-identifier applications-preprod && \
aws-vault exec refunds-dev -- aws rds stop-db-instance --db-instance-identifier caseworker-preprod
