#KMS key for Server Side Encryption (SSE) of S3 buckets
resource "aws_kms_key" "kms_refunds_s3_archive_key" {
  description             = "This key is used to encrypt S3 bucket objects"
  deletion_window_in_days = 10
}

resource "aws_kms_alias" "kms_refunds_s3_archive_key_alias" {
  name          = "alias/${terraform.workspace}-archive-s3-server-side-encryption-key"
  target_key_id = aws_kms_key.kms_refunds_s3_archive_key.key_id
}

#archive bucket - enable SSE by default
resource "aws_s3_bucket" "refunds_archive" {
  bucket = "refunds-${terraform.workspace}-caseworker-archive"
  acl    = "private"

  server_side_encryption_configuration {
    rule {
      apply_server_side_encryption_by_default {
        kms_master_key_id = aws_kms_key.kms_refunds_s3_archive_key.arn
        sse_algorithm     = "aws:kms"
      }
    }
  }

  #all objects immediately subject to Intelligent Tiering allowing for automated use of storage tiers.
  lifecycle_rule {
    id      = "archive"
    enabled = true
    transition {
      days          = 0
      storage_class = "INTELLIGENT_TIERING"
    }
  }

  tags = merge(local.default_tags)

}

# policy that allows breakglass users to put objects
# into the bucket
data "aws_iam_policy_document" "refunds_archive_policy_document" {
  statement {
    sid     = "allowUploadFromSpecificARNs"
    effect  = "Allow"
    actions = ["s3:PutObject"]

    principals {
      identifiers = ["arn:aws:iam::${local.account.account_id}:role/breakglass"]
      type        = "AWS"
    }
    resources = [aws_s3_bucket.refunds_archive.arn, "${aws_s3_bucket.refunds_archive.arn}/*"]
  }
  # ssl requests only
  statement {
    sid     = "DenyNoneSSLRequests"
    effect  = "Deny"
    actions = ["s3:*"]
    resources = [
      aws_s3_bucket.refunds_archive.arn,
      "${aws_s3_bucket.refunds_archive.arn}/*"
    ]
    condition {
      test     = "Bool"
      variable = "aws:SecureTransport"
      values   = [false]
    }

    principals {
      type        = "AWS"
      identifiers = ["*"]
    }
  }
}

resource "aws_s3_bucket_policy" "refunds_archive_policy" {
  depends_on = [aws_s3_bucket.refunds_archive]
  bucket     = aws_s3_bucket.refunds_archive.id
  policy     = data.aws_iam_policy_document.refunds_archive_policy_document.json
}



# set up RDS to S3 export roles
# see https://docs.aws.amazon.com/AmazonRDS/latest/UserGuide/USER_ExportSnapshot.html#USER_ExportSnapshot.SetupIAMRole
resource "aws_iam_role" "refunds_caseworker_export_role" {
  name               = "refunds-caseworker-export-role"
  assume_role_policy = data.aws_iam_policy_document.refunds_caseworker_export_assume_role_policy.json
  tags               = merge(local.default_tags)
}

# Assume role policy for export role
data "aws_iam_policy_document" "refunds_caseworker_export_assume_role_policy" {
  statement {
    actions = ["sts:AssumeRole"]
    effect  = "Allow"
    principals {
      type        = "Service"
      identifiers = ["export.rds.amazonaws.com"]
    }
  }
}

# set up IAM policy for export
resource "aws_iam_policy" "refunds_caseworker_export_policy" {
  name        = "refunds-caseworker-export-policy"
  description = "this policy allows export of RDS snapshots to an S3 bucket"
  policy      = data.aws_iam_policy_document.refunds_caseworker_export_policy.json
}

#policy document setting out access for export role
data "aws_iam_policy_document" "refunds_caseworker_export_policy" {
  statement {
    sid    = "ExportPolicy"
    effect = "Allow"
    actions = [
      "s3:PutObject*",
      "s3:ListBucket",
      "s3:GetObject*",
      "s3:DeleteObject*",
      "s3:GetBucketLocation"
    ]
    resources = [
      "arn:aws:s3:::${aws_s3_bucket.refunds_archive.arn}",
      "arn:aws:s3:::${aws_s3_bucket.refunds_archive.arn}/*"
    ]
  }
}

# create a policy attachment for the export role.
resource "aws_iam_role_policy_attachment" "refunds_caseworker_export_role_policy_attachment" {
  role       = aws_iam_role.refunds_caseworker_export_role.name
  policy_arn = aws_iam_policy.refunds_caseworker_export_policy.arn
}

# Add aws CMK for encrypting the data.
resource "aws_kms_key" "refunds_caseworker_db_archive_key" {
  description             = "Refunds caseworker archive key"
  deletion_window_in_days = 10
  policy                  = data.aws_iam_policy_document.kms_refunds_caseworker_db_archive_key.json
}

resource "aws_kms_alias" "refunds_casewoker_db_archive_key_alias" {
  name          = "alias/${terraform.workspace}-archive-caseworker-db-encryption-key"
  target_key_id = aws_kms_key.refunds_caseworker_db_archive_key.key_id
}

#allow only breakglass role to run this - we do not need op access.
data "aws_iam_policy_document" "kms_refunds_caseworker_db_archive_key" {
  statement {

    sid    = "KMS admin"
    effect = "Allow"
    principals {
      type = "AWS"
      identifiers = [
        "arn:aws:iam::${local.account.account_id}:root",
        "arn:aws:iam::${local.account.account_id}:role/breakglass",
      ]
    }
    resources = ["*"]
    actions = [
      "kms:Encrypt",
      "kms:Decrypt",
      "kms:ReEncrypt",
      "kms:GenerateDataKey*",
      "kms:Create*",
      "kms:Describe*",
      "kms:Enable*",
      "kms:List*",
      "kms:Put*",
      "kms:Update*",
      "kms:Revoke*",
      "kms:Disable*",
      "kms:Get*",
      "kms:Delete*",
      "kms:ScheduleKeyDeletion",
      "kms:CancelKeyDeletion"
    ]
  }
}
