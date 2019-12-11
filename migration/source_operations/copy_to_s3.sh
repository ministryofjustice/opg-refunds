#!/usr/bin/env bash
export ENV_NAME=preproduction
export AWS_DEFAULT_REGION=eu-west-1

aws s3 cp applications.tar s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp cases.tar s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp caseworker.tar s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp finance.tar s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp meris.tar s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp sirius.tar s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
