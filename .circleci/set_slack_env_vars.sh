#! /bin/bash

echo 'export FRONT_DOMAIN="$(jq -r .public_front_fqdn /tmp/environment_pipeline_tasks_config.json)"'
echo 'export CASEWORKER_DOMAIN="$(jq -r .caseworker_front_fqdn /tmp/environment_pipeline_tasks_config.json)"'
echo 'export CLAIM_SERVICE_GOV_UK="$(jq -r .claim_service_gov_uk_fqdn /tmp/environment_pipeline_tasks_config.json)"'
echo 'export CASEWORKER_REFUNDS_OPG_DIGITAL="$(jq -r .caseworker_refunds_opg_digital_fqdn /tmp/environment_pipeline_tasks_config.json)"'
echo 'export COMMIT_MESSAGE="$(git log -1 --pretty=%B)"'
