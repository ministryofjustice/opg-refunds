#!/usr/bin/env bash

set -euxo pipefail

#AWS-VALUT prefix commands
AV_NEW="aws-vault exec moj-refunds-prod --"
AV_OLD="aws-vault exec refunds-prod --"
KMS_KEY_ID="2c74f803-78c2-4a50-aeab-8293c8367ef4"
NEW_AWS_ACCOUNT="805626386523"
OLD_AWS_ACCOUNT="574983609246"
STAGE1_SUFFIX="-stage1"
STAGE2_SUFFIX="-stage2"
STAGE3_SUFFIX="-stage3"

#Start with preprod
APP_DB_INSTANCE_ID="applications-preprod"
CASE_DB_INSTANCE_ID="caseworker-preprod"

# Enter Tokens Early
${AV_OLD} pwd
${AV_NEW} pwd

# Functions
function Create_Snapshots {
  # Skip if already created.
  status=`${AV_OLD} aws rds describe-db-snapshots --db-snapshot-identifier ${APP_DB_INSTANCE_ID}${STAGE1_SUFFIX} | jq -r '.DBSnapshots[0].Status' || true`
  if [[ "$status" != "available" ]]; then
    ${AV_OLD} aws rds create-db-snapshot \
      --db-instance-identifier ${APP_DB_INSTANCE_ID} \
      --db-snapshot-identifier ${APP_DB_INSTANCE_ID}${STAGE1_SUFFIX}
  fi

  status=`${AV_OLD} aws rds describe-db-snapshots --db-snapshot-identifier ${CASE_DB_INSTANCE_ID}${STAGE1_SUFFIX} | jq -r '.DBSnapshots[0].Status' || true`
  if [[ "$status" != "available" ]]; then
    ${AV_OLD} aws rds create-db-snapshot \
      --db-instance-identifier ${CASE_DB_INSTANCE_ID} \
      --db-snapshot-identifier ${CASE_DB_INSTANCE_ID}${STAGE1_SUFFIX}
  fi

  # Wait until created.
  ${AV_OLD} aws rds wait db-snapshot-available \
    --db-snapshot-identifier ${APP_DB_INSTANCE_ID}${STAGE1_SUFFIX} \
    --snapshot-type manual
  ${AV_OLD} aws rds wait db-snapshot-available \
    --db-snapshot-identifier ${CASE_DB_INSTANCE_ID}${STAGE1_SUFFIX} \
    --snapshot-type manual
}

function Copy_Snapshot_With_New_Account_Key {
  # Skip if already created.
  status=`! ${AV_OLD} aws rds describe-db-snapshots --db-snapshot-identifier ${APP_DB_INSTANCE_ID}${STAGE2_SUFFIX} | jq -r '.DBSnapshots[0].Status' || true`
  if [[ "$status" != "available" ]]; then
    ${AV_OLD} aws rds copy-db-snapshot \
      --source-db-snapshot-identifier ${APP_DB_INSTANCE_ID}${STAGE1_SUFFIX} \
      --target-db-snapshot-identifier ${APP_DB_INSTANCE_ID}${STAGE2_SUFFIX} \
      --kms-key-id arn:aws:kms:eu-west-1:${NEW_AWS_ACCOUNT}:key/${KMS_KEY_ID}
  fi

  status=`! ${AV_OLD} aws rds describe-db-snapshots --db-snapshot-identifier ${CASE_DB_INSTANCE_ID}${STAGE2_SUFFIX} | jq -r '.DBSnapshots[0].Status' || true`
  if [[ "$status" != "available" ]]; then
    ${AV_OLD} aws rds copy-db-snapshot \
      --source-db-snapshot-identifier ${CASE_DB_INSTANCE_ID}${STAGE1_SUFFIX} \
      --target-db-snapshot-identifier ${CASE_DB_INSTANCE_ID}${STAGE2_SUFFIX} \
      --kms-key-id arn:aws:kms:eu-west-1:${NEW_AWS_ACCOUNT}:key/${KMS_KEY_ID}
  fi

  # Wait until created.
  ${AV_OLD} aws rds wait db-snapshot-available \
    --db-snapshot-identifier ${APP_DB_INSTANCE_ID}${STAGE2_SUFFIX} \
    --snapshot-type manual
  ${AV_OLD} aws rds wait db-snapshot-available \
    --db-snapshot-identifier ${CASE_DB_INSTANCE_ID}${STAGE2_SUFFIX} \
    --snapshot-type manual
}

function Make_Snapshot_Available_From_Old_Account {
  # Make Snapshots available from old account to new account
  ${AV_OLD} aws rds modify-db-snapshot-attribute \
    --db-snapshot-identifier ${APP_DB_INSTANCE_ID}${STAGE2_SUFFIX} \
    --attribute-name restore \
    --values-to-add '["'${NEW_AWS_ACCOUNT}'"]'
  ${AV_OLD} aws rds modify-db-snapshot-attribute \
    --db-snapshot-identifier ${CASE_DB_INSTANCE_ID}${STAGE2_SUFFIX} \
    --attribute-name restore \
    --values-to-add '["'${NEW_AWS_ACCOUNT}'"]'
}

function Copy_Snapshot_Into_New_Account {
  # Skip if already created.
  status=`! ${AV_NEW} aws rds describe-db-snapshots --db-snapshot-identifier ${APP_DB_INSTANCE_ID}${STAGE3_SUFFIX} | jq -r '.DBSnapshots[0].Status' || true`
  if [[ "$status" != "available" ]]; then
    ${AV_NEW} aws rds copy-db-snapshot \
      --source-db-snapshot-identifier arn:aws:rds:eu-west-1:${OLD_AWS_ACCOUNT}:snapshot:${APP_DB_INSTANCE_ID}${STAGE2_SUFFIX} \
      --target-db-snapshot-identifier ${APP_DB_INSTANCE_ID}${STAGE3_SUFFIX} \
      --kms-key-id arn:aws:kms:eu-west-1:${NEW_AWS_ACCOUNT}:alias/aws/rds
  fi

  status=`! ${AV_NEW} aws rds describe-db-snapshots --db-snapshot-identifier ${CASE_DB_INSTANCE_ID}${STAGE3_SUFFIX} | jq -r '.DBSnapshots[0].Status' || true`
  if [[ "$status" != "available" ]]; then
    ${AV_NEW} aws rds copy-db-snapshot \
      --source-db-snapshot-identifier arn:aws:rds:eu-west-1:${OLD_AWS_ACCOUNT}:snapshot:${CASE_DB_INSTANCE_ID}${STAGE2_SUFFIX} \
      --target-db-snapshot-identifier ${CASE_DB_INSTANCE_ID}${STAGE3_SUFFIX} \
      --kms-key-id arn:aws:kms:eu-west-1:${NEW_AWS_ACCOUNT}:alias/aws/rds
  fi

  ${AV_NEW} aws rds wait db-snapshot-available \
    --db-snapshot-identifier ${APP_DB_INSTANCE_ID}${STAGE3_SUFFIX} \
    --snapshot-type manual
  ${AV_NEW} aws rds wait db-snapshot-available \
    --db-snapshot-identifier ${CASE_DB_INSTANCE_ID}${STAGE3_SUFFIX} \
    --snapshot-type manual
}

function Restore_Postgres_RDS_Cluster_From_Snapshot {
  # Skip if already created.
  status=`! ${AV_NEW} aws rds describe-db-clusters --db-cluster-identifier ${APP_DB_INSTANCE_ID} | jq -r '.DBClusters[0].Status' || true`
  if [[ "$status" != "available" ]]; then
    ${AV_NEW} aws rds restore-db-cluster-from-snapshot \
      --snapshot-identifier arn:aws:rds:eu-west-1:${NEW_AWS_ACCOUNT}:snapshot:${APP_DB_INSTANCE_ID}${STAGE3_SUFFIX} \
      --db-cluster-identifier ${APP_DB_INSTANCE_ID} \
      --engine aurora-postgresql \
      --engine-version 9.6.11 \
      --deletion-protection \
      --vpc-security-group-ids sg-0573be96d868b0322
  fi

  status=`! ${AV_NEW} aws rds describe-db-clusters --db-cluster-identifier ${CASE_DB_INSTANCE_ID} | jq -r '.DBClusters[0].Status' || true`
  if [[ "$status" != "available" ]]; then
    ${AV_NEW} aws rds restore-db-cluster-from-snapshot \
      --snapshot-identifier arn:aws:rds:eu-west-1:${NEW_AWS_ACCOUNT}:snapshot:${CASE_DB_INSTANCE_ID}${STAGE3_SUFFIX} \
      --db-cluster-identifier ${CASE_DB_INSTANCE_ID} \
      --engine aurora-postgresql \
      --engine-version 9.6.11 \
      --deletion-protection \
      --vpc-security-group-ids sg-0573be96d868b0322
  fi

  # wait
  status=unknown
  while [[ "$status" != "available" ]]; do
  sleep 10
  status=`${AV_NEW} aws rds describe-db-clusters --db-cluster-identifier ${APP_DB_INSTANCE_ID} | jq -r '.DBClusters[0].Status' || true`
  done

  status=unknown
  while [[ "$status" != "available" ]]; do
  sleep 10
  status=`${AV_NEW} aws rds describe-db-clusters --db-cluster-identifier ${CASE_DB_INSTANCE_ID} | jq -r '.DBClusters[0].Status' || true`
  done
}

function Cleanup_Snapshots {
  ${AV_OLD} aws rds delete-db-snapshot --db-snapshot-identifier ${APP_DB_INSTANCE_ID}${STAGE1_SUFFIX}
  ${AV_OLD} aws rds delete-db-snapshot --db-snapshot-identifier ${APP_DB_INSTANCE_ID}${STAGE2_SUFFIX}
  ${AV_NEW} aws rds delete-db-snapshot --db-snapshot-identifier ${APP_DB_INSTANCE_ID}${STAGE3_SUFFIX}
  ${AV_OLD} aws rds delete-db-snapshot --db-snapshot-identifier ${CASE_DB_INSTANCE_ID}${STAGE1_SUFFIX}
  ${AV_OLD} aws rds delete-db-snapshot --db-snapshot-identifier ${CASE_DB_INSTANCE_ID}${STAGE2_SUFFIX}
  ${AV_NEW} aws rds delete-db-snapshot --db-snapshot-identifier ${CASE_DB_INSTANCE_ID}${STAGE3_SUFFIX}

  ${AV_OLD} aws rds wait db-snapshot-deleted --db-snapshot-identifier ${APP_DB_INSTANCE_ID}${STAGE1_SUFFIX} --snapshot-type manual
  ${AV_OLD} aws rds wait db-snapshot-deleted --db-snapshot-identifier ${APP_DB_INSTANCE_ID}${STAGE2_SUFFIX} --snapshot-type manual
  ${AV_NEW} aws rds wait db-snapshot-deleted --db-snapshot-identifier ${APP_DB_INSTANCE_ID}${STAGE3_SUFFIX} --snapshot-type manual
  ${AV_OLD} aws rds wait db-snapshot-deleted --db-snapshot-identifier ${CASE_DB_INSTANCE_ID}${STAGE1_SUFFIX} --snapshot-type manual
  ${AV_OLD} aws rds wait db-snapshot-deleted --db-snapshot-identifier ${CASE_DB_INSTANCE_ID}${STAGE2_SUFFIX} --snapshot-type manual
  ${AV_NEW} aws rds wait db-snapshot-deleted --db-snapshot-identifier ${CASE_DB_INSTANCE_ID}${STAGE3_SUFFIX} --snapshot-type manual
}


# Main Script
Create_Snapshots
Copy_Snapshot_With_New_Account_Key
Make_Snapshot_Available_From_Old_Account
Copy_Snapshot_Into_New_Account
Restore_Postgres_RDS_Cluster_From_Snapshot
Cleanup_Snapshots
