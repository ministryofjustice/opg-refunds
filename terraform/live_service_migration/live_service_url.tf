data "aws_route53_zone" "claim-power-of-attorney-refund_service_gov_uk" {
  provider = aws.old_refunds_production
  name     = "claim-power-of-attorney-refund.service.gov.uk"
}

# public front cloudfront distribution
locals {
  public_front_cloudfront_distribution_domain_name = "d3pm2jfxjwrqjc.cloudfront.net"
  public_front_cloudfront_distribution_zone_id     = "Z2FDTNDATAQYW2"
}

# public front load balancers
data "aws_lb" "new_production_public_front" {
  provider = aws.new_refunds_production
  name     = "production-public-front"
}

resource "aws_route53_record" "claim-power-of-attorney-refund_service_gov_uk" {
  provider = aws.old_refunds_production
  zone_id  = "${data.aws_route53_zone.claim-power-of-attorney-refund_service_gov_uk.zone_id}"
  name     = "claim-power-of-attorney-refund.service.gov.uk"
  type     = "A"

  alias {
    evaluate_target_health = false
    # point to old production cloudfront distribution
    name    = local.public_front_cloudfront_distribution_domain_name
    zone_id = local.public_front_cloudfront_distribution_zone_id
    # point to new production
    # name                   = data.aws_lb.new_production_public_front.dns_name
    # zone_id                = data.aws_lb.new_production_public_front.zone_id
  }

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_route53_record" "www_claim-power-of-attorney-refund_service_gov_uk" {
  provider = aws.old_refunds_production
  zone_id  = "${data.aws_route53_zone.claim-power-of-attorney-refund_service_gov_uk.zone_id}"
  name     = "www.claim-power-of-attorney-refund.service.gov.uk"
  type     = "A"

  alias {
    evaluate_target_health = false
    # point to old production cloudfront distribution
    name    = local.public_front_cloudfront_distribution_domain_name
    zone_id = local.public_front_cloudfront_distribution_zone_id
    # point to new production
    # name                   = data.aws_lb.new_production_public_front.dns_name
    # zone_id                = data.aws_lb.new_production_public_front.zone_id
  }

  lifecycle {
    create_before_destroy = true
  }
}

output "live_service_url" {
  value = aws_route53_record.claim-power-of-attorney-refund_service_gov_uk
}


# caseworker front load balancers
data "aws_route53_zone" "refunds_opg_digital" {
  provider = aws.old_refunds_production
  name     = "refunds.opg.digital"
}

data "aws_elb" "old_production_caseworker_front" {
  provider = aws.old_refunds_production
  name     = "caseworker-front-production"
}

data "aws_lb" "new_production_caseworker_front" {
  provider = aws.new_refunds_production
  name     = "production-caseworker-front"
}

resource "aws_route53_record" "caseworker_refunds_opg_digital" {
  provider = aws.old_refunds_production
  zone_id  = "${data.aws_route53_zone.refunds_opg_digital.zone_id}"
  name     = "caseworker.refunds.opg.digital"
  type     = "A"

  alias {
    evaluate_target_health = false
    # point to old production
    name    = data.aws_elb.old_production_caseworker_front.dns_name
    zone_id = data.aws_elb.old_production_caseworker_front.zone_id
    # point to new production
    # name                   = data.aws_lb.new_production_caseworker_front.dns_name
    # zone_id                = data.aws_lb.new_production_caseworker_front.zone_id
  }

  lifecycle {
    create_before_destroy = true
  }
}

output "caseworker_refunds_opg_digital" {
  value = aws_route53_record.caseworker_refunds_opg_digital
}
