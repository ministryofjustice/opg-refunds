# common



# public front secrets
resource "aws_secretsmanager_secret" "opg_refunds_public_front_session_encryption_key" {
  name = "${local.account_name}/opg_refunds_public_front_session_encryption_key"
  tags = local.default_tags
}

resource "aws_secretsmanager_secret" "opg_refunds_public_front_session_encryption_keys" {
  name = "${local.account_name}/opg_refunds_public_front_session_encryption_keys"
  tags = local.default_tags
}

resource "aws_secretsmanager_secret" "opg_refunds_bank_hash_salt" {
  name = "${local.account_name}/opg_refunds_bank_hash_salt"
  tags = local.default_tags
}

resource "aws_secretsmanager_secret" "opg_refunds_notify_api_key" {
  name = "${local.account_name}/opg_refunds_notify_api_key"
  tags = local.default_tags
}

resource "aws_secretsmanager_secret" "opg_refunds_db_applications_write_username" {
  name = "${local.account_name}/opg_refunds_db_applications_write_username"
  tags = local.default_tags
}

resource "aws_secretsmanager_secret" "opg_refunds_db_applications_write_password" {
  name = "${local.account_name}/opg_refunds_db_applications_write_password"
  tags = local.default_tags
}

resource "aws_secretsmanager_secret" "opg_refunds_public_front_beta_link_signature_key" {
  name = "${local.account_name}/opg_refunds_public_front_beta_link_signature_key"
  tags = local.default_tags
}

resource "aws_secretsmanager_secret" "opg_refunds_ad_link_signature_key" {
  name = "${local.account_name}/opg_refunds_ad_link_signature_key"
  tags = local.default_tags
}

resource "aws_secretsmanager_secret" "opg_refunds_public_front_full_key_public" {
  name = "${local.account_name}/opg_refunds_public_front_full_key_public"
  tags = local.default_tags
}

resource "aws_secretsmanager_secret" "opg_refunds_public_front_bank_key_public" {
  name = "${local.account_name}/opg_refunds_public_front_bank_key_public"
  tags = local.default_tags
}

resource "aws_secretsmanager_secret" "opg_refunds_public_front_bank_key_public_data" {
  name = "${local.account_name}/opg_refunds_public_front_bank_key_public_data"
  tags = local.default_tags
}

resource "aws_secretsmanager_secret" "opg_refunds_public_front_full_key_public_data" {
  name = "${local.account_name}/opg_refunds_public_front_full_key_public_data"
  tags = local.default_tags
}


# ingestion and db secrets
resource "aws_secretsmanager_secret" "postgres_password" {
  name = "${local.account_name}/postgres_password"
  tags = local.default_tags
}


resource "aws_secretsmanager_secret" "opg_refunds_db_applications_migration_password" {
  name = "${local.account_name}/opg_refunds_db_applications_migration_password"
  tags = local.default_tags
}

resource "aws_secretsmanager_secret" "opg_refunds_db_cases_full_password" {
  name = "${local.account_name}/opg_refunds_db_cases_full_password"
  tags = local.default_tags
}

resource "aws_secretsmanager_secret" "opg_refunds_db_cases_migration_password" {
  name = "${local.account_name}/opg_refunds_db_cases_migration_password"
  tags = local.default_tags
}

resource "aws_secretsmanager_secret" "opg_refunds_db_sirius_full_password" {
  name = "${local.account_name}/opg_refunds_db_sirius_full_password"
  tags = local.default_tags
}

resource "aws_secretsmanager_secret" "opg_refunds_db_sirius_migration_password" {
  name = "${local.account_name}/opg_refunds_db_sirius_migration_password"
  tags = local.default_tags
}

resource "aws_secretsmanager_secret" "opg_refunds_db_meris_full_password" {
  name = "${local.account_name}/opg_refunds_db_meris_full_password"
  tags = local.default_tags
}

resource "aws_secretsmanager_secret" "opg_refunds_db_meris_migration_password" {
  name = "${local.account_name}/opg_refunds_db_meris_migration_password"
  tags = local.default_tags
}

resource "aws_secretsmanager_secret" "opg_refunds_db_finance_full_password" {
  name = "${local.account_name}/opg_refunds_db_finance_full_password"
  tags = local.default_tags
}

resource "aws_secretsmanager_secret" "opg_refunds_db_finance_migration_password" {
  name = "${local.account_name}/opg_refunds_db_finance_migration_password"
  tags = local.default_tags
}

resource "aws_secretsmanager_secret" "opg_refunds_caseworker_admin_username" {
  name = "${local.account_name}/opg_refunds_caseworker_admin_username"
  tags = local.default_tags
}

resource "aws_secretsmanager_secret" "opg_refunds_caseworker_admin_password" {
  name = "${local.account_name}/opg_refunds_caseworker_admin_password"
  tags = local.default_tags
}
