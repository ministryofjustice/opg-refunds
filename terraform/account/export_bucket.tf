
module "allow_ip_list" {
  source = "git@github.com:ministryofjustice/terraform-aws-moj-ip-whitelist.git"
}
# allow public read on the bucket and objects,
# but access will be limited by policy to moj_sites
resource "aws_s3_bucket" "refunds_export" {
  bucket = "refunds-${terraform.workspace}-exported"
  acl    = "public-read"
  tags   = merge(local.default_tags)

}

# policy that allow operator & breakglass uses to put objects
# into the bucket and people on the moj vpn to download
data "aws_iam_policy_document" "refunds_export_policy_document" {
  statement {
    sid     = "allowUploadFromSpecificARNs"
    effect  = "Allow"
    actions = ["s3:PutObject"]

    principals {
      identifiers = ["arn:aws:iam::${local.account.account_id}:role/operator", "arn:aws:iam::${local.account.account_id}:role/breakglass"]
      type        = "AWS"
    }
    resources = [aws_s3_bucket.refunds_export.arn, "${aws_s3_bucket.refunds_export.arn}/*"]
  }
  # add network restriction here
  statement {
    sid    = "allowReadingFromMoJVPN"
    effect = "Allow"
    actions = [
      "s3:GetObject",
      "s3:ListBucket"
    ]
    resources = [
      aws_s3_bucket.refunds_export.arn,
      "${aws_s3_bucket.refunds_export.arn}/*"
    ]
    condition {
      test     = "IpAddress"
      values   = module.allow_ip_list.moj_sites
      variable = "AWS:SourceIp"
    }
    principals {
      type        = "AWS"
      identifiers = ["*"]
    }

  }
  # ssl requests only
  statement {
    sid     = "DenyNoneSSLRequests"
    effect  = "Deny"
    actions = ["s3:*"]
    resources = [
      aws_s3_bucket.refunds_export.arn,
      "${aws_s3_bucket.refunds_export.arn}/*"
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


resource "aws_s3_bucket_policy" "refunds_export_policy" {
  depends_on = [aws_s3_bucket.refunds_export]
  bucket     = aws_s3_bucket.refunds_export.id
  policy     = data.aws_iam_policy_document.refunds_export_policy_document.json
}
