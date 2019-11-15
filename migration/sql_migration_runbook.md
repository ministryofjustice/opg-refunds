# old prod instance
# dump sql
```bash
./old-refunds-dump-tables.sh
```

# put files
```bash
aws s3 cp applications.sql s3://lpa-refunds-production-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp cases.sql s3://lpa-refunds-production-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp caseworker.sql s3://lpa-refunds-production-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp finance.sql s3://lpa-refunds-production-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp meris.sql s3://lpa-refunds-production-sql-migration/ --sse --acl bucket-owner-full-control
aws s3 cp sirius.sql s3://lpa-refunds-production-sql-migration/ --sse --acl bucket-owner-full-control
```
# new prod instance
# add disk
```bash
sudo lsblk
sudo mkfs.ext4  /dev/xvdf
mkdir -p /mnt
sudo mount /dev/xvdf /mnt
df -h
sudo vi /etc/fstab
(insert `/dev/xvdf /mnt ext4`)
sudo umount /mnt
df -h
sudo mount -a
cd /mnt
```

# install deps
```bash
sudo yum update
sudo yum install git
sudo git clone --single-branch --branch LPA-3461-s3-bucket-sql-data  https://github.com/ministryofjustice/opg-refunds.git
```

# check db access
```bash
psql -h $OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME -U root applications
psql -h $CASES_DB_ENDPOINT -U root cases
```
# pull sql files from s3
```bash
aws s3 sync s3://lpa-refunds-production-sql-migration /mnt/sql
```

# import sql dump
```bash
PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id production/opg_refunds_db_cases_migration_password | jq -r .'SecretString') psql -h $CASES_DB_ENDPOINT -U cases_migration cases < cases.sql

PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id production/opg_refunds_db_applications_migration_password | jq -r .'SecretString') psql -h $OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME -U applications_migration sirius < applications.sql

PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id production/opg_refunds_db_sirius_migration_password | jq -r .'SecretString') psql -h $CASES_DB_ENDPOINT -U sirius_migration sirius < sirius.sql

PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id production/opg_refunds_db_meris_migration_password | jq -r .'SecretString') psql -h $CASES_DB_ENDPOINT -U meris_migration meris < meris.sql

PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id production/opg_refunds_db_finance_migration_password | jq -r .'SecretString') psql -h $CASES_DB_ENDPOINT -U finance_migration finance < finance.sql
```
