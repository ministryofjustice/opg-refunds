
# caseworker front load balancers
data "aws_route53_zone" "refunds_opg_digital" {
  provider = aws.old_refunds_production
  name     = "refunds.opg.digital"
}

locals {
  caseworker_url_dns_target = {
    alias = {
      name    = aws_lb.caseworker_front.dns_name
      zone_id = aws_lb.caseworker_front.zone_id
    }
  }
}

resource "aws_route53_record" "caseworker_refunds_opg_digital" {
  provider = aws.old_refunds_production
  zone_id  = data.aws_route53_zone.refunds_opg_digital.zone_id
  name     = "${local.dns_prefix}caseworker.refunds.opg.digital"
  type     = "A"

  alias {
    evaluate_target_health = false
    name                   = local.caseworker_url_dns_target.alias.name
    zone_id                = local.caseworker_url_dns_target.alias.zone_id
  }

  lifecycle {
    create_before_destroy = true
  }
}
