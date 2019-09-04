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
