# common

resource "aws_kms_key" "opg_refunds_secrets_key" {
  enable_key_rotation = true
}



# public front secrets
resource "aws_secretsmanager_secret" "opg_refunds_public_front_session_encryption_key" {
  name       = "${local.account_name}/opg_refunds_public_front_session_encryption_key"
  tags       = merge(local.default_tags, local.public_front_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_public_front_session_encryption_keys" {
  name       = "${local.account_name}/opg_refunds_public_front_session_encryption_keys"
  tags       = merge(local.default_tags, local.public_front_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_bank_hash_salt" {
  name       = "${local.account_name}/opg_refunds_bank_hash_salt"
  tags       = merge(local.default_tags, local.shared_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_notify_api_key" {
  name       = "${local.account_name}/opg_refunds_notify_api_key"
  tags       = merge(local.default_tags, local.shared_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_db_applications_write_username" {
  name       = "${local.account_name}/opg_refunds_db_applications_write_username"
  tags       = merge(local.default_tags, local.public_front_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_db_applications_write_password" {
  name       = "${local.account_name}/opg_refunds_db_applications_write_password"
  tags       = merge(local.default_tags, local.public_front_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_public_front_beta_link_signature_key" {
  name       = "${local.account_name}/opg_refunds_public_front_beta_link_signature_key"
  tags       = merge(local.default_tags, local.public_front_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_ad_link_signature_key" {
  name       = "${local.account_name}/opg_refunds_ad_link_signature_key"
  tags       = merge(local.default_tags, local.shared_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_public_front_full_key_public" {
  name       = "${local.account_name}/opg_refunds_public_front_full_key_public"
  tags       = merge(local.default_tags, local.public_front_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_public_front_bank_key_public" {
  name       = "${local.account_name}/opg_refunds_public_front_bank_key_public"
  tags       = merge(local.default_tags, local.public_front_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_public_front_bank_key_public_data" {
  name       = "${local.account_name}/opg_refunds_public_front_bank_key_public_data"
  tags       = merge(local.default_tags, local.public_front_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_public_front_full_key_public_data" {
  name       = "${local.account_name}/opg_refunds_public_front_full_key_public_data"
  tags       = merge(local.default_tags, local.public_front_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

# ingestion and db secrets
resource "aws_secretsmanager_secret" "postgres_password" {
  name       = "${local.account_name}/postgres_password"
  tags       = merge(local.default_tags, local.shared_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_db_applications_full_password" {
  name       = "${local.account_name}/opg_refunds_db_applications_full_password"
  tags       = merge(local.default_tags, local.public_front_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_db_applications_migration_password" {
  name       = "${local.account_name}/opg_refunds_db_applications_migration_password"
  tags       = merge(local.default_tags, local.public_front_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_db_cases_full_password" {
  name       = "${local.account_name}/opg_refunds_db_cases_full_password"
  tags       = merge(local.default_tags, local.caseworker_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_db_cases_migration_password" {
  name       = "${local.account_name}/opg_refunds_db_cases_migration_password"
  tags       = merge(local.default_tags, local.caseworker_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_db_sirius_full_password" {
  name       = "${local.account_name}/opg_refunds_db_sirius_full_password"
  tags       = merge(local.default_tags, local.caseworker_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_db_sirius_migration_password" {
  name       = "${local.account_name}/opg_refunds_db_sirius_migration_password"
  tags       = merge(local.default_tags, local.caseworker_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_db_meris_full_password" {
  name       = "${local.account_name}/opg_refunds_db_meris_full_password"
  tags       = merge(local.default_tags, local.caseworker_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_db_meris_migration_password" {
  name       = "${local.account_name}/opg_refunds_db_meris_migration_password"
  tags       = merge(local.default_tags, local.caseworker_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_db_finance_full_password" {
  name       = "${local.account_name}/opg_refunds_db_finance_full_password"
  tags       = merge(local.default_tags, local.caseworker_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_db_finance_migration_password" {
  name       = "${local.account_name}/opg_refunds_db_finance_migration_password"
  tags       = merge(local.default_tags, local.caseworker_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_caseworker_admin_username" {
  name       = "${local.account_name}/opg_refunds_caseworker_admin_username"
  tags       = merge(local.default_tags, local.caseworker_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_caseworker_admin_password" {
  name       = "${local.account_name}/opg_refunds_caseworker_admin_password"
  tags       = merge(local.default_tags, local.caseworker_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

# SSCL data
resource "aws_secretsmanager_secret" "opg_refunds_sscl_entity" {
  name       = "${local.account_name}/opg_refunds_sscl_entity"
  tags       = merge(local.default_tags, local.caseworker_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_sscl_cost_centre" {
  name       = "${local.account_name}/opg_refunds_sscl_cost_centre"
  tags       = merge(local.default_tags, local.caseworker_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_sscl_account" {
  name       = "${local.account_name}/opg_refunds_sscl_account"
  tags       = merge(local.default_tags, local.caseworker_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}

resource "aws_secretsmanager_secret" "opg_refunds_sscl_analysis" {
  name       = "${local.account_name}/opg_refunds_sscl_analysis"
  tags       = merge(local.default_tags, local.caseworker_component_tag)
  kms_key_id = aws_kms_key.opg_refunds_secrets_key.arn
}
