resource "aws_s3_bucket" "sql_migration" {
  bucket = "lpa-refunds-${terraform.workspace}-sql-migration"
  acl    = "private"
  tags   = local.default_tags

  server_side_encryption_configuration {
    rule {
      apply_server_side_encryption_by_default {
        sse_algorithm = "aws:kms"
      }
    }
  }
}

resource "aws_s3_bucket_policy" "sql_migration" {
  bucket = aws_s3_bucket.sql_migration.id
  policy = data.aws_iam_policy_document.cross_account_access.json
}

data "aws_iam_policy_document" "cross_account_access" {
  statement {
    sid = "SqlMigrationBucketPutAccess"

    resources = [
      "${aws_s3_bucket.sql_migration.arn}",
      "${aws_s3_bucket.sql_migration.arn}/*",
    ]

    effect = "Allow"
    actions = [
      "s3:PutObject",
      "s3:PutObjectAcl"
    ]

    principals {
      identifiers = ["arn:aws:iam::574983609246:role/caseworker-api.production"]

      type = "AWS"
    }
  }
}
