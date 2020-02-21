# Using cloud9 for database queries

clone the lpa-refunds repository
```
git clone --single-branch --branch LPA-3461-s3-bucket-sql-data  https://github.com/ministryofjustice/opg-refunds.git
```

run the cloud9 init script.
```
. cloud9/cloud9_init.sh <workspace>
```
note the use of `. cloud9/`. This sources the file so that environment variables are set.

add cloud9 ingress rules for databases.
```
cd terraform/cloud9_ingress
terraform init
terraform apply -var cloud9_ip=$CLOUD9_IP
```

connect to a database
```
psql -h $OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME -U root applications
psql -h $CASES_DB_ENDPOINT -U root cases
```

when finished, remove the security group rules
```
cd terraform/cloud9_ingress
terraform init
terraform destroy
```
