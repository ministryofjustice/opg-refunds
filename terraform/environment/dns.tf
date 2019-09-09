data "aws_route53_zone" "opg_service_justice_gov_uk" {
  provider = "aws.management"
  name     = "opg.service.justice.gov.uk"
}

resource "aws_service_discovery_private_dns_namespace" "internal" {
  name = "${local.environment}-internal"
  vpc  = "${data.aws_vpc.default.id}"
}

//-------------------------------------------------------------
// Public Front

resource "aws_route53_record" "public_front" {
  provider = "aws.management"
  zone_id  = "${data.aws_route53_zone.opg_service_justice_gov_uk.zone_id}"
  name     = "${local.dns_namespace_env}${local.account.public_front_dns}"
  type     = "A"

  alias {
    evaluate_target_health = false
    name                   = "${aws_lb.public_front.dns_name}"
    zone_id                = "${aws_lb.public_front.zone_id}"
  }

  lifecycle {
    create_before_destroy = true
  }
}

//-------------------------------------------------------------
// Caseworker Front

resource "aws_route53_record" "caseworker_front" {
  provider = "aws.management"
  zone_id  = "${data.aws_route53_zone.opg_service_justice_gov_uk.zone_id}"
  name     = "${local.dns_namespace_env}${local.account.caseworker_front_dns}"
  type     = "A"

  alias {
    evaluate_target_health = false
    name                   = "${aws_lb.caseworker_front.dns_name}"
    zone_id                = "${aws_lb.caseworker_front.zone_id}"
  }

  lifecycle {
    create_before_destroy = true
  }
}
