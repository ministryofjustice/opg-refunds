#!/bin/bash
quit () {
    echo >&2 "$@"
    exit 1
}

[ "$#" -eq 1 ] || quit "stack name required, $# provided"

echo "working on stack $0..."
echo "bringing up ASG desired count on stack $0..."

aws-vault exec identity -- terraform init
aws-vault exec identity -- terraform apply -auto-approve -var stack=$0 -var state_up=true

# start
aws-vault exec refunds-dev -- aws rds start-db-instance --db-instance-identifier applications-$0 && \
aws-vault exec refunds-dev -- aws rds start-db-instance --db-instance-identifier caseworker-$0
