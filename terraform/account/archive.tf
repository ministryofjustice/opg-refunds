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



