data "http" "icanhazip" {
  url = "https://icanhazip.com"
}

output "public_ip" {
  value = local.local_ip_cidr
}

locals {
  local_ip_cidr = "${chomp(data.http.icanhazip.body)}/32"
}

data "aws_security_group" "caseworker_front_loadbalancer" {
  name = "${local.environment}-caseworker-front-loadbalancer"
}

resource "aws_security_group_rule" "caseworker_front_ci_ingress" {
  count             = local.account.allow_ingress_modification ? 1 : 0
  type              = "ingress"
  from_port         = 443
  to_port           = 443
  protocol          = "tcp"
  cidr_blocks       = [local.local_ip_cidr]
  security_group_id = data.aws_security_group.caseworker_front_loadbalancer.id
  description       = "ci_ingress"
}

data "aws_security_group" "public_front_loadbalancer" {
  name = "${local.environment}-public-front-loadbalancer"
}

resource "aws_security_group_rule" "public_front_ci_ingress" {
  count             = local.account.allow_ingress_modification ? 1 : 0
  type              = "ingress"
  from_port         = 443
  to_port           = 443
  protocol          = "tcp"
  cidr_blocks       = [local.local_ip_cidr]
  security_group_id = data.aws_security_group.public_front_loadbalancer.id
  description       = "ci_ingress"
}
