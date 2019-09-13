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

data "aws_acm_certificate" "certificate_public_front" {
  domain = local.account.public_front_certificate_domain_name
}

data "aws_acm_certificate" "certificate_caseworker_front" {
  domain = local.account.caseworker_front_certificate_domain_name
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

data "aws_iam_policy" "enforce_vpc_endpoint_access" {
  arn = "arn:aws:iam::${local.account.id}:policy/enforce-vpc-endpoint-access"
}
