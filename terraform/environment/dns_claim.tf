data "aws_route53_zone" "claim-power-of-attorney-refund_service_gov_uk" {
  provider = aws.old_refunds_production
  name     = "claim-power-of-attorney-refund.service.gov.uk"
}

locals {
  public_front_cloudfront_distribution_domain_name = "d3pm2jfxjwrqjc.cloudfront.net"
  public_front_cloudfront_distribution_zone_id     = "Z2FDTNDATAQYW2"

  claim_url_dns_target_dev_preprod = {
    alias = {
      name    = aws_lb.public_front.dns_name
      zone_id = aws_lb.public_front.zone_id
    }
  }

  claim_url_dns_target_production = {
    alias = {
      name    = local.public_front_cloudfront_distribution_domain_name
      zone_id = local.public_front_cloudfront_distribution_zone_id
    }
  }
  claim_url_dns_target = local.account == "production" ? local.claim_url_dns_target_production : local.claim_url_dns_target_dev_preprod
}



//-------------------------------------------------------------
// Claim - points to public front
resource "aws_route53_record" "claim-power-of-attorney-refund_service_gov_uk" {
  provider = aws.old_refunds_production
  zone_id  = data.aws_route53_zone.claim-power-of-attorney-refund_service_gov_uk.zone_id
  name     = "${local.dns_namespace_env}claim-power-of-attorney-refund.service.gov.uk"
  type     = "A"

  alias {
    evaluate_target_health = false
    name                   = local.claim_url_dns_target.alias.name
    zone_id                = local.claim_url_dns_target.alias.zone_id
  }

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_route53_record" "www_claim-power-of-attorney-refund_service_gov_uk" {
  provider = aws.old_refunds_production
  zone_id  = data.aws_route53_zone.claim-power-of-attorney-refund_service_gov_uk.zone_id
  name     = "www.${local.dns_namespace_env}claim-power-of-attorney-refund.service.gov.uk"
  type     = "A"

  alias {
    evaluate_target_health = false
    name                   = local.claim_url_dns_target.alias.name
    zone_id                = local.claim_url_dns_target.alias.zone_id
  }

  lifecycle {
    create_before_destroy = true
  }
}
