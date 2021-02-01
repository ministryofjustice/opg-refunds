resource "aws_lb_target_group" "public_front" {
  name                 = "${local.environment}-public-front"
  port                 = 80
  protocol             = "HTTP"
  target_type          = "ip"
  vpc_id               = data.aws_vpc.default.id
  deregistration_delay = 0
  health_check {
    enabled             = true
    interval            = 30
    path                = "/robots.txt"
    healthy_threshold   = 3
    unhealthy_threshold = 3
    matcher             = 200
  }
  depends_on = [aws_lb.public_front]
  tags       = merge(local.default_tags, local.public_front_component_tag)
}

resource "aws_lb" "public_front" {
  name               = "${local.environment}-public-front"
  internal           = false
  load_balancer_type = "application"
  subnets            = data.aws_subnet_ids.public.ids
  tags               = merge(local.default_tags, local.public_front_component_tag)

  security_groups = [
    aws_security_group.public_front_loadbalancer.id,
  ]

  access_logs {
    bucket  = data.aws_s3_bucket.access_log.bucket
    prefix  = "${local.environment}-public_front"
    enabled = true
  }
}

resource "aws_lb_listener" "public_front_loadbalancer" {
  load_balancer_arn = aws_lb.public_front.arn
  port              = "443"
  protocol          = "HTTPS"
  ssl_policy        = "ELBSecurityPolicy-TLS-1-2-Ext-2018-06"
  certificate_arn   = data.aws_acm_certificate.certificate_public_front.arn

  default_action {
    target_group_arn = aws_lb_target_group.public_front.arn
    type             = "forward"
  }
}

resource "aws_lb_listener_certificate" "claim_power_of_attorney_refund_service_gov_uk" {
  listener_arn    = aws_lb_listener.public_front_loadbalancer.arn
  certificate_arn = data.aws_acm_certificate.claim_power_of_attorney_refund_service_gov_uk.arn
}

resource "aws_ssm_parameter" "maintenance_switch" {
  name            = "${local.environment}_enable_maintenance"
  type            = "String"
  value           = "false"
  description     = "values of either 'true' or 'false' only"
  allowed_pattern = "^(true|false)"
  overwrite       = true
  lifecycle {
    ignore_changes = [value]
  }
}

# maintenance site switching
locals {
  path_pattern = {
    field  = "path-pattern"
    values = ["/maintenance"]
  }
  host_pattern = {
    field  = "host-header"
    values = [aws_route53_record.claim-power-of-attorney-refund_service_gov_uk.fqdn]
  }
  rule_condition = aws_ssm_parameter.maintenance_switch.value ? local.host_pattern : local.path_pattern

}

# maint mode rule.
resource "aws_lb_listener_rule" "public_front_maintenance" {
  listener_arn = aws_lb_listener.public_front_loadbalancer.arn
  priority     = 2
  action {
    type = "fixed-response"

    fixed_response {
      content_type = "text/html"
      message_body = file("${path.module}/maintenance/maintenance.html")
      status_code  = "503"
    }
  }

  condition {
    field  = local.rule_condition.field
    values = local.rule_condition.values
  }
}

# we are no longer live. therefore will fix the response for internal and external.
resource "aws_lb_listener_rule" "public_front_shutdown" {
  listener_arn = aws_lb_listener.public_front_loadbalancer.arn
  priority     = 1
  action {
    type = "redirect"
    redirect {
      host        = "www.gov.uk"
      path        = "/power-of-attorney-refund"
      status_code = "HTTP_301"
    }
  }
  condition {
    field  = "host-header"
    values = [aws_route53_record.public_front.fqdn, aws_route53_record.claim-power-of-attorney-refund_service_gov_uk.fqdn]
  }
}

resource "aws_security_group" "public_front_loadbalancer" {
  name        = "${local.environment}-public-front-loadbalancer"
  description = "public front load balancer access"
  vpc_id      = data.aws_vpc.default.id
  tags        = merge(local.default_tags, local.public_front_component_tag)
}

resource "aws_security_group_rule" "public_front_loadbalancer_ingress" {
  type              = "ingress"
  from_port         = 443
  to_port           = 443
  protocol          = "tcp"
  cidr_blocks       = module.whitelist.moj_sites
  security_group_id = aws_security_group.public_front_loadbalancer.id
}

// Allow http traffic in to be redirected to https
resource "aws_security_group_rule" "public_front_loadbalancer_ingress_http" {
  type              = "ingress"
  from_port         = 80
  to_port           = 80
  protocol          = "tcp"
  cidr_blocks       = ["0.0.0.0/0"]
  security_group_id = aws_security_group.public_front_loadbalancer.id
}

resource "aws_security_group_rule" "public_front_loadbalancer_egress" {
  type              = "egress"
  from_port         = 0
  to_port           = 0
  protocol          = "-1"
  cidr_blocks       = ["0.0.0.0/0"]
  security_group_id = aws_security_group.public_front_loadbalancer.id
}


//------------------------------------------------
// HTTP Redirect

resource "aws_lb_listener" "public_front_loadbalancer_http_redirect" {
  load_balancer_arn = aws_lb.public_front.arn
  port              = "80"
  protocol          = "HTTP"

  default_action {
    type = "redirect"

    redirect {
      port        = 443
      protocol    = "HTTPS"
      status_code = "HTTP_301"
    }
  }
}
