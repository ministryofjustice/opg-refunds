data "aws_vpc" "default" {
  default = true
}

data "aws_subnet_ids" "private" {
  vpc_id = data.aws_vpc.default.id

  tags = {
    Name = "private*"
  }
}

data "aws_subnet_ids" "public" {
  vpc_id = data.aws_vpc.default.id

  tags = {
    Name = "public*"
  }
}

data "aws_s3_bucket" "access_log" {
  bucket = "lpa-refunds-${local.account_name}-lb-access-logs"
}
locals {
  refunds_opg_service_justice_gov_uk_name = trimsuffix(data.aws_route53_zone.refunds_opg_service_justice_gov_uk.name, ".")
}
data "aws_acm_certificate" "certificate_public_front" {
  domain = "public-front.${local.refunds_opg_service_justice_gov_uk_name}"
}

data "aws_acm_certificate" "certificate_caseworker_front" {
  domain = "caseworker.${local.refunds_opg_service_justice_gov_uk_name}"
}

data "aws_acm_certificate" "claim_power_of_attorney_refund_service_gov_uk" {
  provider = aws.old_refunds_production
  domain   = "claim-power-of-attorney-refund.service.gov.uk"
}

data "aws_acm_certificate" "caseworker_refunds_opg_digital" {
  provider = aws.old_refunds_production
  domain   = "caseworker.refunds.opg.digital"
}

module "whitelist" {
  source = "git@github.com:ministryofjustice/terraform-aws-moj-ip-whitelist.git"
}

data "aws_ip_ranges" "cloudfront" {
  services = ["cloudfront"]
}

data "aws_ip_ranges" "ec2" {
  services = ["ec2"]
}

data "aws_kms_alias" "bank_encrypt_decrypt" {
  name = "alias/lpa-refunds-${local.account_name}-bank-encrypt-decrypt"
}

data "aws_iam_policy" "restrict_to_vpc_endpoints" {
  arn = "arn:aws:iam::${local.account.account_id}:policy/restrict-to-vpc-endpoints"
}
