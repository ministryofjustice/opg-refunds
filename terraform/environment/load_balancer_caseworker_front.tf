resource "aws_lb_target_group" "caseworker_front" {
  name                 = "${local.environment}-caseworker-front"
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
  depends_on = [aws_lb.caseworker_front]
  tags       = local.default_tags
}

resource "aws_lb" "caseworker_front" {
  name               = "${local.environment}-caseworker-front"
  internal           = false
  load_balancer_type = "application"
  subnets            = data.aws_subnet_ids.public.ids
  tags               = local.default_tags

  security_groups = [
    aws_security_group.caseworker_front_loadbalancer.id,
  ]

  access_logs {
    bucket  = data.aws_s3_bucket.access_log.bucket
    prefix  = "${local.environment}-caseworker_front"
    enabled = true
  }
}

resource "aws_lb_listener" "caseworker_front_loadbalancer" {
  load_balancer_arn = aws_lb.caseworker_front.arn
  port              = "443"
  protocol          = "HTTPS"
  ssl_policy        = "ELBSecurityPolicy-TLS-1-2-Ext-2018-06"
  certificate_arn   = data.aws_acm_certificate.certificate_caseworker_front.arn

  default_action {
    target_group_arn = aws_lb_target_group.caseworker_front.arn
    type             = "forward"
  }
}

resource "aws_lb_listener_certificate" "caseworker_refunds_opg_digital" {
  listener_arn    = aws_lb_listener.caseworker_front_loadbalancer.arn
  certificate_arn = data.aws_acm_certificate.caseworker_refunds_opg_digital.arn
}

resource "aws_security_group" "caseworker_front_loadbalancer" {
  name        = "${local.environment}-caseworker-front-loadbalancer"
  description = "caseworker front load balancer access"
  vpc_id      = data.aws_vpc.default.id
  tags        = local.default_tags
}

resource "aws_security_group_rule" "caseworker_front_loadbalancer_ingress" {
  type              = "ingress"
  from_port         = 443
  to_port           = 443
  protocol          = "tcp"
  cidr_blocks       = module.whitelist.moj_sites
  security_group_id = aws_security_group.caseworker_front_loadbalancer.id
}

resource "aws_security_group_rule" "caseworker_front_loadbalancer_ingress_ithc" {
  count             = local.environment == "preproduction" ? 1 : 0
  type              = "ingress"
  from_port         = 443
  to_port           = 443
  protocol          = "tcp"
  cidr_blocks       = ["54.37.241.156/30"]
  security_group_id = aws_security_group.caseworker_front_loadbalancer.id
}

// Allow http traffic in to be redirected to https
resource "aws_security_group_rule" "caseworker_front_loadbalancer_ingress_http" {
  type              = "ingress"
  from_port         = 80
  to_port           = 80
  protocol          = "tcp"
  cidr_blocks       = ["0.0.0.0/0"]
  security_group_id = aws_security_group.caseworker_front_loadbalancer.id
}

resource "aws_security_group_rule" "caseworker_front_loadbalancer_egress" {
  type              = "egress"
  from_port         = 0
  to_port           = 0
  protocol          = "-1"
  cidr_blocks       = ["0.0.0.0/0"]
  security_group_id = aws_security_group.caseworker_front_loadbalancer.id
}


//------------------------------------------------
// HTTP Redirect

resource "aws_lb_listener" "caseworker_front_loadbalancer_http_redirect" {
  load_balancer_arn = aws_lb.caseworker_front.arn
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
