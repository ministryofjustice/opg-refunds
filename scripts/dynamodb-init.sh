#!/usr/bin/env bash
#create dynamodb on local dynamo container
if [[ -f ../lpa-refund-local-dev/develop/env/front.env ]]
then
    source ../lpa-refund-local-dev/develop/env/front.env

    export AWS_DEFAULT_REGION=${OPG_REFUNDS_PUBLIC_FRONT_SESSION_DYNAMODB_REGION}
    export AWS_ACCESS_KEY_ID AWS_SECRET_ACCESS_KEY

    aws dynamodb create-table \
      --endpoint-url http://localhost:8000 \
      --attribute-definitions AttributeName=id,AttributeType=S \
      --table-name ${OPG_REFUNDS_PUBLIC_FRONT_SESSION_DYNAMODB_TABLE} \
      --key-schema AttributeName=id,KeyType=HASH \
      --provisioned-throughput ReadCapacityUnits=10,WriteCapacityUnits=10
# not supported on local dynamodb as at June 2017
# needs to be reviewed in the future
#    aws dynamodb update-time-to-live \
#      --endpoint-url http://localhost:8000 \
#      --table-name ${OPG_REFUNDS_PUBLIC_FRONT_SESSION_DYNAMODB_TABLE} \
#      --time-to-live-specification '{"Enabled": true, "AttributeName": "expires"}'


    aws dynamodb create-table \
      --endpoint-url http://localhost:8000 \
      --attribute-definitions AttributeName=id,AttributeType=N \
      --table-name ${OPG_REFUNDS_PUBLIC_BETA_LINK_DYNAMODB_TABLE} \
      --key-schema AttributeName=id,KeyType=HASH \
      --provisioned-throughput ReadCapacityUnits=10,WriteCapacityUnits=10

fi
