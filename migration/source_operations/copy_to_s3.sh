#!/usr/bin/env bash
export ENV_NAME=preproduction
export AWS_DEFAULT_REGION=eu-west-1

aws s3 cp /mnt/sql/applications.sql s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp /mnt/sql/cases.sql s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp /mnt/sql/caseworker.sql s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp /mnt/sql/finance.sql s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp /mnt/sql/meris.sql s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp /mnt/sql/sirius.sql s3://lpa-refunds-$ENV_NAME-sql-migration/ --sse --acl bucket-owner-full-control
