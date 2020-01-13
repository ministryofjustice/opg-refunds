# Refunds Migration Runbook

The lpa refunds service is is being migrated to new infrastructure.
The major differences are

- replacing EC2 compute running custom AMIs with new containers deployed to ECS.
- replacing RDS PostgreSQL instances with RDS Aurora clusters to enable the use of serverless Aurora databases in the future on development environments.

## Target Environment URLs
<https://preproduction.caseworker.refunds.opg.digital/sign-in>

<https://preproduction.claim-power-of-attorney-refund.service.gov.uk/when-were-fees-paid>

### Actions

replace <SOURCE_ENVIRONMENT_NAME> with production

replace <TARGET_ENVIRONMENT_NAME> with production

# !! During production migration, terraform for the public front load balancer needs to be updated to point to new production

-----

## Roll back procedure

### set asg desired count to normal operation

```bash
tf apply -var stack=<SOURCE_ENVIRONMENT_NAME> -var state_up=true
```

### restart databases if stopped

```bash
aws-vault exec refunds-prod -- aws rds start-db-instance --db-instance-identifier applications-<SOURCE_ENVIRONMENT_NAME>
aws-vault exec refunds-prod -- aws rds start-db-instance --db-instance-identifier caseworker-<SOURCE_ENVIRONMENT_NAME>
```

### Set <SOURCE_ENVIRONMENT_NAME> to live mode

In parameter store set enable maintenance to `false`

update load balancer

```bash
tf apply
```

----

## Migration

### setup ec2 instance for sql data extract

1. connect to vpn
2. in old accounts create ec2 instance

| Parameter           | Value                                               |
| --------------------|-----------------------------------------------------|
| Type                | T2.Medium                                           |
| OS                  | Ubuntu 18.04                                        |
| IAM Roles           | caseworker-api-<SOURCE_ENVIRONMENT_NAME>            |
| Volumes             | /dev/xvdb, 30GiB, Encrypted, Default Key            |
| Security Groups     | application-rds-<SOURCE_ENVIRONMENT_NAME>,          |
|                     |   caseworker-api-<SOURCE_ENVIRONMENT_NAME>,         |
|                     |   caseworker-rds-<SOURCE_ENVIRONMENT_NAME>,         |
|                     |   jumphost-client-prod-vpc                          |
| Tags                | name:<SOURCE_ENVIRONMENT_NAME>-migration-instance   |
| Key Pair            | refunds-migration-source-instance                   |

3. ssh-add key

```bash
chmod 600 ~/Downloads/refunds-migration-instance.pem
ssh-add ~/Downloads/refunds-migration-instance.pem
```

4. connect to old prod instance

```bash
ssh <USER@IPADDRESS>
```

5. prepare and mount encrypted volume

```bash
sudo lsblk
sudo mkfs.ext4  /dev/xvdb
mkdir -p /mnt
sudo vi /etc/fstab
(insert `/dev/xvdb /mnt ext4`)
sudo umount /mnt
df -h
sudo mount -a
cd /mnt
```

```bash
git clone https://github.com/ministryofjustice/opg-refunds.git
sudo apt-get update
sudo sudo apt-get install postgresql postgresql-contrib python3-pip awscli -y
sudo pip3 install awscli --upgrade --user
```

---

### setup ec2 instance for sql data load

6. In new accounts create ec2 instance role called migration-instance

| Parameter           | Value                                               |
| --------------------|-----------------------------------------------------|
| Name                | migration-instance                                  |
| Policy              | administrator                                       |

7. create ec2 instance

| Parameter           | Value                                               |
| --------------------|-----------------------------------------------------|
| Type                | T2.Medium                                           |
| OS                  | Amazon Linux 2                                      |
| IAM Roles           | migration_instance                                  |
| Volumes             | /dev/sdb, 30GiB, Encrypted, Default Key             |
| Auto-assign IP      | True                                                |
| Security Groups     | Create: migration-instance                          |
| Security Group Rule | Ingress via vpn ip only                             |
| Tags                | name:migration-instance                             |
| Key Pair            | refunds-migration-target-instance                   |

8. assign security groups to migration instance

| Parameter           | Value                                               |
| --------------------|-----------------------------------------------------|
| Name                | <TARGET_ENVIRONMENT_NAME>-caseworker-rds-cluster-client   |
|                     | <TARGET_ENVIRONMENT_NAME>-applications-rds-cluster-client |

```bash
chmod 600 ~/Downloads/refunds-migration-target-instance.pem
ssh-add ~/Downloads/refunds-migration-target-instance.pem  
```

---

9. connect to new prod instance

```bash
ssh <USER@IPADDRESS>
```

---

10. prepare and mount encrypted volume

```bash
sudo su
lsblk
mkfs.ext4  /dev/xvdb
mkdir -p /mnt
vi /etc/fstab
(insert `/dev/xvdb /mnt ext4`)
mount -a
df -h
cd /mnt
yum remove php* -y
yum install php73 php73-pdo php73-pgsql postgresql jq git -y
amazon-linux-extras install postgresql10
```

11. pull code

```bash
git clone https://github.com/ministryofjustice/opg-refunds.git
cd /mnt/opg-refunds
git checkout refunds-migration

alias l="ls -al"
```

---

### Set notify key to dev mode

12. in secrets manager update <TARGET_ENVIRONMENT_NAME>/opg_refunds_notify_api_key to new value

```bash
dev key: REDACTED
```

### Delete Target Databases

13. set role to breakglass and workspace to <TARGET_ENVIRONMENT_NAME>
14. Run terraform taint and apply

```bash
tf taint aws_rds_cluster.applications
tf taint aws_rds_cluster.caseworker
tf taint "aws_rds_cluster_instance.applications_cluster_instances[0]"
tf taint "aws_rds_cluster_instance.caseworker_cluster_instances[0]"
tf taint aws_ecs_service.caseworker_api
tf taint aws_ecs_service.caseworker_front
tf taint aws_ecs_service.ingestion
tf taint aws_ecs_service.public_front

tf apply
```

---

### Update DNS

# !! During production migration, terraform for the public front load balancer needs to be updated to point to new production

15. In parameter store set enable maintenance to `true`

16. Update load balancer

```bash
tf apply
```

---

### Set ASGs to 0

17. Empty state and import resources to manage

```bash
tf state rm aws_autoscaling_group.caseworker_api
tf state rm aws_autoscaling_group.caseworker_front
tf state rm aws_autoscaling_group.public_front
```

```bash
tf import aws_autoscaling_group.public_front front-<SOURCE_ENVIRONMENT_NAME>
tf import aws_autoscaling_group.caseworker_front caseworker-front-<SOURCE_ENVIRONMENT_NAME>
tf import aws_autoscaling_group.caseworker_api caseworker-api-<SOURCE_ENVIRONMENT_NAME>
```

18. Take down stack

```bash
tf apply -var stack=<SOURCE_ENVIRONMENT_NAME> -var state_up=false
```

---

### Extract sql Data

19. Connect to <SOURCE_ENVIRONMENT_NAME>

```bash
ssh <USER@IPADDRESS>
sudo su
cd /mnt/opg-refunds/migration
```

20. edit migration/old-refunds-dump-tables.sh and add passwords for databases

```bash
nano /mnt/opg-refunds/migration/source_operations/pg_dump_tables.sh
```

21. update with these values

| Name                                      | Value    |
| ------------------------------------------|----------|
| OPG_REFUNDS_DB_APPLICATIONS_FULL_PASSWORD | REDACTED |
| OPG_REFUNDS_DB_CASES_FULL_PASSWORD        | REDACTED |
| OPG_REFUNDS_DB_SIRIUS_FULL_PASSWORD       | REDACTED |

22. Run pg_dump script

```bash
/mnt/opg-refunds/migration/source_operations/pg_dump_tables.sh
```

23. Run check_tables and sequences_nextval scripts for later validation and updates

```bash
/mnt/opg-refunds/migration/source_operations/check_tables.sh
/mnt/opg-refunds/migration/source_operations/sequences_nextval.sh
```

---

### Transfer sql data to s3

24. Copy dumped sql files to S3

```bash
/mnt/opg-refunds/migration/source_operations/copy_to_s3.sh

```

---

### Load sql data

25. connect to new prod instance

```bash
ssh <USER@IPADDRESS>
```

26. Set env vars for PG

```bash
source /mnt/opg-refunds/migration/instance_config/env_vars.sh
source /mnt/opg-refunds/migration/instance_config/alias.sh
```

27. pull data from s3

```bash
aws s3 sync s3://lpa-refunds-$ENV_NAME-sql-migration /mnt/sql
```

28. load data into databases

```bash
time /mnt/opg-refunds/migration/target_operations/load_sql.sh
```

29. modify the alter sequence sql scripts to match the source sequence nextval number

- cases_alter_sequence.sql

30. run alter_sequence script

```bash
/mnt/opg-refunds/migration/target_operations/list_sequences.sh
/mnt/opg-refunds/migration/target_operations/alter_sequences.sh
/mnt/opg-refunds/migration/target_operations/list_sequences.sh
/mnt/opg-refunds/migration/target_operations/check_tables.sh
```

---

### Stop Old Databases

31. Run aws cli commands to stop <SOURCE_ENVIRONMENT_NAME> databases

```bash
aws-vault exec refunds-prod -- aws rds stop-db-instance --db-instance-identifier applications-<SOURCE_ENVIRONMENT_NAME> && \
aws-vault exec refunds-prod -- aws rds stop-db-instance --db-instance-identifier caseworker-<SOURCE_ENVIRONMENT_NAME>
```

---

### Set notify key to live mode

32. in secrets manager update <TARGET_ENVIRONMENT_NAME>/opg_refunds_notify_api_key to new value

```bash
prod key: REDACTED
```

---

### Update dns

33. In parameter store set enable maintenance to `false`

34. update load balancer and notify key

```bash
tf apply
```
