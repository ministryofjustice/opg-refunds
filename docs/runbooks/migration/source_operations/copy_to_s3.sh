#!/usr/bin/env bash
export ENV_NAME=production
export AWS_DEFAULT_REGION=eu-west-1

aws s3 cp /mnt/sql/applications.tar s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp /mnt/sql/cases.tar s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp /mnt/sql/caseworker.tar s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp /mnt/sql/finance.tar s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp /mnt/sql/meris.tar s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp /mnt/sql/sirius.tar s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
