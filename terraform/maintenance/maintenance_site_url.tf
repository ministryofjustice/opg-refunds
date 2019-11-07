resource "aws_route53_record" "maintenance_claim-power-of-attorney-refund_service_gov_uk_a" {
  provider = aws.old_refunds_production
  zone_id  = data.aws_route53_zone.claim-power-of-attorney-refund_service_gov_uk.zone_id
  name     = "maintenance.claim-power-of-attorney-refund.service.gov.uk"
  type     = "A"

  alias {
    evaluate_target_health = false
    name                   = aws_cloudfront_distribution.maintenance.domain_name
    zone_id                = aws_cloudfront_distribution.maintenance.hosted_zone_id
  }

  lifecycle {
    create_before_destroy = true
  }
}

output "maintenance_service_url" {
  value = aws_route53_record.maintenance_claim-power-of-attorney-refund_service_gov_uk_a
}
