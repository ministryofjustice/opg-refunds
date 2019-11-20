data "aws_route53_zone" "claim-power-of-attorney-refund_service_gov_uk" {
  provider = aws.old_refunds_production
  name     = "claim-power-of-attorney-refund.service.gov.uk"
}
