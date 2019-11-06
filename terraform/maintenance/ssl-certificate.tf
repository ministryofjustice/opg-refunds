resource "aws_route53_record" "certificate_validation_maintenance_cloudfront_dn" {
  provider = aws.old_refunds_production
  name     = aws_acm_certificate.maintenance_cloudfront.domain_validation_options.0.resource_record_name
  type     = aws_acm_certificate.maintenance_cloudfront.domain_validation_options.0.resource_record_type
  zone_id  = data.aws_route53_zone.claim-power-of-attorney-refund_service_gov_uk.id
  records  = [aws_acm_certificate.maintenance_cloudfront.domain_validation_options.0.resource_record_value]
  ttl      = 300
}

resource "aws_route53_record" "certificate_validation_maintenance_cloudfront_san_www" {
  provider = aws.old_refunds_production
  name     = aws_acm_certificate.maintenance_cloudfront.domain_validation_options.1.resource_record_name
  type     = aws_acm_certificate.maintenance_cloudfront.domain_validation_options.1.resource_record_type
  zone_id  = data.aws_route53_zone.claim-power-of-attorney-refund_service_gov_uk.id
  records  = [aws_acm_certificate.maintenance_cloudfront.domain_validation_options.1.resource_record_value]
  ttl      = 300
}

resource "aws_route53_record" "cv_maintenance_cloudfront_san_maintenance" {
  provider = aws.old_refunds_production
  name     = aws_acm_certificate.maintenance_cloudfront.domain_validation_options.2.resource_record_name
  type     = aws_acm_certificate.maintenance_cloudfront.domain_validation_options.2.resource_record_type
  zone_id  = data.aws_route53_zone.claim-power-of-attorney-refund_service_gov_uk.id
  records  = [aws_acm_certificate.maintenance_cloudfront.domain_validation_options.2.resource_record_value]
  ttl      = 300
}

resource "aws_acm_certificate_validation" "maintenance_cloudfront" {
  provider        = aws.us_east_1
  certificate_arn = aws_acm_certificate.maintenance_cloudfront.arn
  validation_record_fqdns = [
    aws_route53_record.certificate_validation_maintenance_cloudfront_dn.fqdn,
    aws_route53_record.certificate_validation_maintenance_cloudfront_san_www.fqdn,
    aws_route53_record.cv_maintenance_cloudfront_san_maintenance.fqdn,
  ]

}

resource "aws_acm_certificate" "maintenance_cloudfront" {
  provider                  = aws.us_east_1
  domain_name               = "claim-power-of-attorney-refund.service.gov.uk"
  subject_alternative_names = ["www.claim-power-of-attorney-refund.service.gov.uk", "maintenance.claim-power-of-attorney-refund.service.gov.uk"]
  validation_method         = "DNS"
  options {
    certificate_transparency_logging_preference = "ENABLED"
  }
  tags = local.default_tags
}
