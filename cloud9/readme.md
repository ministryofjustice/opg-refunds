# Using cloud9 for database queries

clone the lpa-refunds repository
```
git clone --single-branch --branch  LPA-3376  https://github.com/ministryofjustice/opg-refunds.git
```

run the cloud9 init script.
```
. cloud9/cloud9_init.sh
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
psql -h caseworker-81-lpa3376.cluster-c4i63kewcgwk.eu-west-1.rds.amazonaws.com -U root postgres
```

<!-- git clone --single-branch --branch  LPA-3334-ecs-move  https://github.com/ministryofjustice/opg-refunds.git -->

81-lpa3376