#!/bin/bash

quit () {
    echo >&2 "$@"
    exit 1
}

[ "$#" -eq 1 ] || quit "stack name required, $# provided"

echo "working on stack $0..."
echo "setting ASGs desired count to 0 on stack $0..."

aws-vault exec identity -- terraform init
echo "following will prompt for stack name to target"
aws-vault exec identity -- terraform apply -auto-approve -var stack=$0 -var state_up=false

read -p "Press enter to stop DB instances"

echo "stopping DB instances on stack $0..."
# stop DB instancees
aws-vault exec refunds-dev -- aws rds stop-db-instance --db-instance-identifier applications-$0 && \
aws-vault exec refunds-dev -- aws rds stop-db-instance --db-instance-identifier caseworker-$0
