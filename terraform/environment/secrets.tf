# common




# front secrets
data "aws_secretsmanager_secret" "opg_refunds_public_front_session_encryption_key" {
  name = "${local.account_name}/opg_refunds_public_front_session_encryption_key"
}

data "aws_secretsmanager_secret" "opg_refunds_public_front_session_encryption_keys" {
  name = "${local.account_name}/opg_refunds_public_front_session_encryption_keys"
}

data "aws_secretsmanager_secret" "opg_refunds_bank_hash_salt" {
  name = "${local.account_name}/opg_refunds_bank_hash_salt"
}

data "aws_secretsmanager_secret" "opg_refunds_notify_api_key" {
  name = "${local.account_name}/opg_refunds_notify_api_key"
}

data "aws_secretsmanager_secret" "opg_refunds_db_applications_write_username" {
  name = "${local.account_name}/opg_refunds_db_applications_write_username"
}

data "aws_secretsmanager_secret" "opg_refunds_db_applications_write_password" {
  name = "${local.account_name}/opg_refunds_db_applications_write_password"
}

data "aws_secretsmanager_secret" "opg_refunds_public_front_beta_link_signature_key" {
  name = "${local.account_name}/opg_refunds_public_front_beta_link_signature_key"
}

data "aws_secretsmanager_secret" "opg_refunds_ad_link_signature_key" {
  name = "${local.account_name}/opg_refunds_ad_link_signature_key"
}

data "aws_secretsmanager_secret" "opg_refunds_public_front_full_key_public" {
  name = "${local.account_name}/opg_refunds_public_front_full_key_public"
}

data "aws_secretsmanager_secret" "opg_refunds_public_front_bank_key_public" {
  name = "${local.account_name}/opg_refunds_public_front_bank_key_public"
}

data "aws_secretsmanager_secret" "opg_refunds_public_front_bank_key_public_data" {
  name = "${local.account_name}/opg_refunds_public_front_bank_key_public_data"
}

data "aws_secretsmanager_secret" "opg_refunds_public_front_full_key_public_data" {
  name = "${local.account_name}/opg_refunds_public_front_full_key_public_data"
}


# ingestion and db secrets
data "aws_secretsmanager_secret" "postgres_password" {
  name = "${local.account_name}/postgres_password"
}

data "aws_secretsmanager_secret_version" "postgres_password" {
  secret_id = data.aws_secretsmanager_secret.postgres_password.id
}

data "aws_secretsmanager_secret" "opg_refunds_db_applications_full_password" {
  name = "${local.account_name}/opg_refunds_db_applications_full_password"
}

data "aws_secretsmanager_secret" "opg_refunds_db_applications_migration_password" {
  name = "${local.account_name}/opg_refunds_db_applications_migration_password"
}

data "aws_secretsmanager_secret" "opg_refunds_db_cases_full_password" {
  name = "${local.account_name}/opg_refunds_db_cases_full_password"
}

data "aws_secretsmanager_secret" "opg_refunds_db_cases_migration_password" {
  name = "${local.account_name}/opg_refunds_db_cases_migration_password"
}

data "aws_secretsmanager_secret" "opg_refunds_db_sirius_full_password" {
  name = "${local.account_name}/opg_refunds_db_sirius_full_password"
}

data "aws_secretsmanager_secret" "opg_refunds_db_sirius_migration_password" {
  name = "${local.account_name}/opg_refunds_db_sirius_migration_password"
}

data "aws_secretsmanager_secret" "opg_refunds_db_meris_full_password" {
  name = "${local.account_name}/opg_refunds_db_meris_full_password"
}

data "aws_secretsmanager_secret" "opg_refunds_db_meris_migration_password" {
  name = "${local.account_name}/opg_refunds_db_meris_migration_password"
}

data "aws_secretsmanager_secret" "opg_refunds_db_finance_full_password" {
  name = "${local.account_name}/opg_refunds_db_finance_full_password"
}

data "aws_secretsmanager_secret" "opg_refunds_db_finance_migration_password" {
  name = "${local.account_name}/opg_refunds_db_finance_migration_password"
}

data "aws_secretsmanager_secret" "opg_refunds_caseworker_admin_username" {
  name = "${local.account_name}/opg_refunds_caseworker_admin_username"
}

data "aws_secretsmanager_secret" "opg_refunds_caseworker_admin_password" {
  name = "${local.account_name}/opg_refunds_caseworker_admin_password"
}

# sscl secrets
data "aws_secretsmanager_secret" "opg_refunds_sscl_entity" {
  name = "${local.account_name}/opg_refunds_sscl_entity"
}

data "aws_secretsmanager_secret" "opg_refunds_sscl_cost_centre" {
  name = "${local.account_name}/opg_refunds_sscl_cost_centre"
}

data "aws_secretsmanager_secret" "opg_refunds_sscl_account" {
  name = "${local.account_name}/opg_refunds_sscl_account"
}

data "aws_secretsmanager_secret" "opg_refunds_sscl_analysis" {
  name = "${local.account_name}/opg_refunds_sscl_analysis"
}

