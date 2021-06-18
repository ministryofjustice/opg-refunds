# allow public read on the bucket and objects,
# but access will be limited by policy to moj_sites
resource "aws_s3_bucket" "refunds_archive" {
  bucket = "refunds-${terraform.workspace}-caseworker-archive"
  acl    = "private"
  tags   = merge(local.default_tags)

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
  assume_role_policy = data.aws_iam_policy_document.refunds_caseworker_export_assume_role_policy
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
  policy      = data.aws_iam_policy_document.refunds_caseworker_export_policy
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
  role       = aws_iam_role.refunds_caseworker_export_role
  policy_arn = aws_iam_policy.refunds_caseworker_export_policy
}
