
resource "aws_kms_key" "rds_migration" {
  description = "RDS account migration"
  policy      = data.aws_iam_policy_document.external_cmk_access.json
}


resource "aws_kms_alias" "rds_migration" {
  name          = "alias/rds_account_migrations"
  target_key_id = aws_kms_key.rds_migration.key_id
}


# Allow account-write to access key
data "aws_iam_policy_document" "external_cmk_access" {

  statement {
    sid = "Allow an external account old-refund-prod to use this CMK"

    actions = [
      "kms:Encrypt",
      "kms:Decrypt",
      "kms:ReEncrypt*",
      "kms:GenerateDataKey*",
      "kms:DescribeKey",
    ]

    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::574983609246:root"]
    }

    resources = [
      "*",
    ]
  }


  statement {
    sid = "Allow administration of the key"

    actions = [
      "kms:*",
    ]

    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::${local.account.account_id}:root"]
    }

    resources = [
      "*",
    ]
  }


}

