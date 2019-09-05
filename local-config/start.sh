#!/bin/sh
set +e

echo Starting local config

DYNAMODN_ENDPOINT=http://${AWS_ENDPOINT_DYNAMODB}
/usr/local/bin/waitforit -address=tcp://${AWS_ENDPOINT_DYNAMODB} -timeout 60 -retry 6000 -debug

# ----------------------------------------------------------
# Add any setup here that is performed with Terraform in AWS.

aws dynamodb create-table \
--attribute-definitions AttributeName=id,AttributeType=S \
--table-name Sessions \
--key-schema AttributeName=id,KeyType=HASH \
--provisioned-throughput ReadCapacityUnits=100,WriteCapacityUnits=100 \
--region eu-west-1 \
--endpoint $DYNAMODN_ENDPOINT

aws dynamodb create-table \
--attribute-definitions AttributeName=id,AttributeType=S \
--table-name CaseworkerSessions \
--key-schema AttributeName=id,KeyType=HASH \
--provisioned-throughput ReadCapacityUnits=100,WriteCapacityUnits=100 \
--region eu-west-1 \
--endpoint $DYNAMODN_ENDPOINT

aws dynamodb create-table \
--attribute-definitions AttributeName=id,AttributeType=S \
--table-name Locks \
--key-schema AttributeName=id,KeyType=HASH \
--provisioned-throughput ReadCapacityUnits=100,WriteCapacityUnits=100 \
--region eu-west-1 \
--endpoint $DYNAMODN_ENDPOINT
