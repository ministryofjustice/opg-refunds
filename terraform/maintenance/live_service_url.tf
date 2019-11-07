data "aws_route53_zone" "claim-power-of-attorney-refund_service_gov_uk" {
  provider = aws.old_refunds_production
  name     = "claim-power-of-attorney-refund.service.gov.uk"
}

# front load balancers
data "aws_elb" "old_production_public_front" {
  provider = aws.old_refunds_production
  name     = "front-production"
}

data "aws_elb" "old_production_caseworker_front" {
  provider = aws.old_refunds_production
  name     = "caseworker-front-production"
}

data "aws_lb" "new_production_public_front" {
  provider = aws.new_refunds_production
  name     = "production-public-front"
}

data "aws_lb" "new_production_caseworker_front" {
  provider = aws.new_refunds_production
  name     = "production-caseworker-front"
}

# output "old_production_public_front" {
#   value = data.aws_elb.old_production_public_front
# }
# output "old_production_caseworker_front" {
#   value = data.aws_elb.old_production_caseworker_front
# }
# output "new_production_public_front" {
#   value = data.aws_lb.new_production_public_front
# }
# output "new_production_caseworker_front" {
#   value = data.aws_lb.new_production_caseworker_front
# }






