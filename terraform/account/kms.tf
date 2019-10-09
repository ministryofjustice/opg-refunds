resource "aws_kms_key" "bank_encrypt_decrypt" {
  description             = "lpa-refunds-${local.account_name}-bank-encrypt-decrypt"
  deletion_window_in_days = 7
  enable_key_rotation     = true
  is_enabled              = true
  key_usage               = "ENCRYPT_DECRYPT"
  policy                  = data.aws_iam_policy_document.bank_encrypt_decrypt.json
}

data "aws_iam_policy_document" "bank_encrypt_decrypt" {
  statement {
    sid = "Enable IAM User Permissions"

    actions = [
      "kms:*",
    ]

    resources = [
      "*",
    ]

    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::${local.account.account_id}:root"]
    }
  }
}

resource "aws_kms_alias" "bank_encrypt_decrypt" {
  name          = "alias/lpa-refunds-${local.account_name}-bank-encrypt-decrypt"
  target_key_id = "${aws_kms_key.bank_encrypt_decrypt.key_id}"
}
