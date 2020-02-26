#! /bin/bash
function main() {
  install_tools
  infer_account ${1:?}
  add_rds_sgs ${1:?}
  postgresql ${1:?}
  setup_info
}


function install_tools() {
  sudo yum install postgresql jq -y
}

function infer_account() {
  case "${1:?}" in
  'preproduction')
  export ACCOUNT="${1:?}"
  ;;
  'production')
  export ACCOUNT="${1:?}"
  ;;
  *)
  export ACCOUNT="development"
  ;;
  esac
}

function add_rds_sgs() {
  local current_sg_name
  local current_sg_id
  local environment_name=$(echo ${1:?} | awk '{print tolower($0)}')
  local applications_rds_client_sg
  local caseworker_rds_client_sg
  local CURRENT_SG_IDS=()

  echo "getting current sg names and instance id..."
  current_sg_names=$(curl -s http://169.254.169.254/latest/meta-data/security-groups)
  instance_id=$(curl -s http://169.254.169.254/latest/meta-data/instance-id)

  echo "getting app rds sg id..."
  applications_rds_client_sg=$(
    aws ec2 describe-security-groups \
    --filters Name=group-name,Values="${environment_name}-applications-rds-cluster-client" \
    --query "SecurityGroups[0].GroupId" \
    --output text
  )

  echo "getting caseworker rds sg id..."
  caseworker_rds_client_sg=$(
    aws ec2 describe-security-groups \
    --filters Name=group-name,Values="${environment_name}-caseworker-rds-cluster-client" \
    --query "SecurityGroups[0].GroupId" \
    --output text
  )

  if [[ $current_sg_names =~ "${environment_name}-caseworker-rds-cluster-client" ] && [ $current_sg_names =~ "${environment_name}-applications-rds-cluster-client" ]]; then
    echo "Security Groups already attached..."
    return
  fi

  echo "getting current (cloud9) sg id..."
  current_sg_id=$(
    aws ec2 describe-security-groups \
    --filters Name=group-name,Values=$current_sg_names \
    --query "SecurityGroups[0].GroupId" \
    --output text
  )

  echo "modifying cloud9..."
  aws ec2 modify-instance-attribute --groups "${current_sg_id}" "${caseworker_rds_client_sg}" "${applications_rds_client_sg}" --instance-id ${instance_id}
}

function postgresql() {
  local environment_name=$(echo ${1:?} | awk '{print tolower($0)}')
  export AWS_DEFAULT_REGION=eu-west-1
  export APPLICATIONS_HOST=$(aws rds describe-db-clusters --db-cluster-identifier applications-${environment_name} | jq -r .'DBClusters'[0].'Endpoint')
  export CASEWORKER_HOST=$(aws rds describe-db-clusters --db-cluster-identifier caseworker-${environment_name} | jq -r .'DBClusters'[0].'Endpoint')
  export PGUSER=root
  export PGPASSWORD=$(aws secretsmanager get-secret-value --secret-id ${ACCOUNT}/postgres_password | jq -r .'SecretString')
}

function setup_info() {
  echo ""
  echo "------------------------------------------------------------------------------------"
  echo "Applications PostgreSQL Instance set to $APPLICATIONS_HOST"
  echo "------------------------------------------------------------------------------------"
  echo "Caseworker PostgreSQL Instance set to $CASEWORKER_HOST"
  echo "------------------------------------------------------------------------------------"
  echo ""
}

main ${1:?}
