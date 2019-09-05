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

# database secrets
# data "aws_secretsmanager_secret" "api_rds_username" {
#   name = "${local.account_name}/api_rds_username"
# }

# data "aws_secretsmanager_secret" "api_rds_password" {
#   name = "${local.account_name}/api_rds_password"
# }

# data "aws_secretsmanager_secret_version" "api_rds_username" {
#   secret_id = data.aws_secretsmanager_secret.api_rds_username.id
# }

# data "aws_secretsmanager_secret_version" "api_rds_password" {
#   secret_id = data.aws_secretsmanager_secret.api_rds_password.id
# }
