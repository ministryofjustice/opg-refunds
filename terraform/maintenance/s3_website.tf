resource "aws_s3_bucket" "claim-power-of-attorney-refund_service_gov_uk" {
  bucket        = "claim-power-of-attorney-refund.service.gov.uk"
  region        = "eu-central-1"
  request_payer = "BucketOwner"
  acl           = "public-read"
  website {
    error_document = "index.html"
    index_document = "index.html"
  }

  tags     = local.default_tags
  provider = aws.eu_central_1
}

resource "aws_s3_bucket_policy" "claim-power-of-attorney-refund_service_gov_uk" {
  provider = aws.eu_central_1
  bucket   = aws_s3_bucket.claim-power-of-attorney-refund_service_gov_uk.id
  policy   = data.aws_iam_policy_document.claim-power-of-attorney-refund_service_gov_uk.json
}

data "aws_iam_policy_document" "claim-power-of-attorney-refund_service_gov_uk" {
  statement {
    sid = "PublicReadGetObject"

    actions = ["s3:GetObject"]
    principals {
      type        = "*"
      identifiers = ["*"]
    }

    resources = ["arn:aws:s3:::claim-power-of-attorney-refund.service.gov.uk/*"]
  }
}
