# Using cloud9 for database queries

clone the lpa-refunds repository
```
git clone --single-branch --branch  LPA-3376  https://github.com/ministryofjustice/opg-refunds.git
```

run the cloud9 init script.
```
cloud9/cloud9_init.sh
```

add cloud9 ingress rules for databases.
```
cd terraform/cloud9_ingress
terraform init
terraform apply
```

connect to a database
```
psql -h caseworker-81-lpa3376.cluster-c4i63kewcgwk.eu-west-1.rds.amazonaws.com -U root postgres
```

<!-- git clone --single-branch --branch  LPA-3334-ecs-move  https://github.com/ministryofjustice/opg-refunds.git -->