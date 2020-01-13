data "aws_route53_zone" "claim-power-of-attorney-refund_service_gov_uk" {
  provider = aws.old_refunds_production
  name     = "claim-power-of-attorney-refund.service.gov.uk"
}

locals {
  claim_url_dns_target = {
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
}



//-------------------------------------------------------------
// Claim - points to public front
resource "aws_route53_record" "claim-power-of-attorney-refund_service_gov_uk" {
  provider = aws.old_refunds_production
  zone_id  = data.aws_route53_zone.claim-power-of-attorney-refund_service_gov_uk.zone_id
  name     = "${local.dns_prefix}claim-power-of-attorney-refund.service.gov.uk"
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
  name     = "www.${local.dns_prefix}claim-power-of-attorney-refund.service.gov.uk"
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
