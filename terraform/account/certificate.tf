data "aws_route53_zone" "opg_service_justice_gov_uk" {
  provider = "aws.management"
  name     = "opg.service.justice.gov.uk"
}

//------------------------
// Public Front Certificates

resource "aws_route53_record" "certificate_validation_public_front" {
  provider = "aws.management"
  name     = aws_acm_certificate.certificate_public_front.domain_validation_options.0.resource_record_name
  type     = aws_acm_certificate.certificate_public_front.domain_validation_options.0.resource_record_type
  zone_id  = data.aws_route53_zone.opg_service_justice_gov_uk.zone_id
  records  = [aws_acm_certificate.certificate_public_front.domain_validation_options.0.resource_record_value]
  ttl      = 60
}

resource "aws_acm_certificate_validation" "certificate_public_front" {
  certificate_arn         = aws_acm_certificate.certificate_public_front.arn
  validation_record_fqdns = [aws_route53_record.certificate_validation_public_front.fqdn]
}

resource "aws_acm_certificate" "certificate_public_front" {
  domain_name       = local.account.public_front_certificate_domain_name
  validation_method = "DNS"
  lifecycle {
    create_before_destroy = true
  }
  tags = local.default_tags
}


//------------------------
// Public Front Cloudfront Certificates

resource "aws_acm_certificate" "cloudfront_public_front" {
  domain_name       = local.account.public_front_certificate_domain_name
  validation_method = "DNS"

  lifecycle {
    create_before_destroy = true
  }
  provider = aws.us_east_1
  tags     = local.default_tags
}

resource "aws_acm_certificate_validation" "cloudfront_public_front" {
  certificate_arn         = aws_acm_certificate.cloudfront_public_front.arn
  validation_record_fqdns = [aws_route53_record.certificate_validation_public_front.fqdn]
  provider                = aws.us_east_1
}

