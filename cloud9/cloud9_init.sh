#! /bin/bash

for arg in "$@"
do
    if [ "$arg" == "--help" ] || [ "$arg" == "-h" ]
    then
        echo "Set up cloud9 environment with some tools. takes an environment name as an argument"
        exit 0
    fi
done
 
# install php dependencies
sudo yum remove php* -y
sudo yum install php73 php73-pdo php73-pgsql postgresql jq -y

# install terraform
export TF_VERSION=0.12.10
export TF_SHA256SUM=2215208822f1a183fb57e24289de417c9b3157affbe8a5e520b768edbcb420b4
export TF_VAR_default_role=operator
export TF_VAR_management_role=operator
export TF_CLI_ARGS_init="-upgrade=true  -reconfigure"
export TF_WORKSPACE=$1
curl -sfSO https://releases.hashicorp.com/terraform/${TF_VERSION}/terraform_${TF_VERSION}_linux_amd64.zip
echo "${TF_SHA256SUM} terraform_${TF_VERSION}_linux_amd64.zip" > SHA256SUMS
sha256sum -c --status SHA256SUMS
sudo unzip -o terraform_${TF_VERSION}_linux_amd64.zip -d /bin
terraform -version

# set db env vars
export ENV_NAME=$(echo "$TF_WORKSPACE" | awk '{print tolower($0)}')
export OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME=$(aws rds describe-db-clusters --db-cluster-identifier applications-$ENV_NAME | jq -r .'DBClusters'[0].'Endpoint')
export OPG_REFUNDS_DB_APPLICATIONS_PORT=5432
export OPG_REFUNDS_DB_FINANCE_HOSTNAME=$(aws rds describe-db-clusters --db-cluster-identifier caseworker-$ENV_NAME | jq -r .'DBClusters'[0].'Endpoint')
export OPG_REFUNDS_DB_FINANCE_PORT=5432
export OPG_REFUNDS_DB_SIRIUS_HOSTNAME=$(aws rds describe-db-clusters --db-cluster-identifier caseworker-$ENV_NAME | jq -r .'DBClusters'[0].'Endpoint')
export OPG_REFUNDS_DB_SIRIUS_PORT=5432
export OPG_REFUNDS_DB_MERIS_HOSTNAME=$(aws rds describe-db-clusters --db-cluster-identifier caseworker-$ENV_NAME | jq -r .'DBClusters'[0].'Endpoint')
export OPG_REFUNDS_DB_MERIS_PORT=5432
export POSTGRES_USER=root
export PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id development/postgres_password | jq -r .'SecretString')

# Set cloud9 IP for ingress
export CLOUD9_IP=$(ip route get 1 | awk '{print $NF;exit}')

