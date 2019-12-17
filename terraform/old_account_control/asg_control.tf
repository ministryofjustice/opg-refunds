variable "stack" {
  description = "name of stack"
}

variable "state_up" {
  description = "setting state of the stack to up or down"
  default     = false
}

locals {
  up_state = {
    public_front = 3
    caseworker   = 2
  }
  down_state = {
    public_front = 0
    caseworker   = 0
  }
  desired = var.state_up ? local.up_state : local.down_state
}

resource "aws_autoscaling_group" "public_front" {
  provider         = aws.old_refunds_production
  name             = "front-${var.stack}"
  max_size         = local.desired.public_front
  min_size         = 0
  desired_capacity = local.desired.public_front
  lifecycle {
    ignore_changes = [
      force_delete,
      tag,
      health_check_grace_period,
      launch_configuration,
      termination_policies,
      wait_for_capacity_timeout,
    ]
  }
}

resource "aws_autoscaling_group" "caseworker_front" {
  provider         = aws.old_refunds_production
  name             = "caseworker-front-${var.stack}"
  max_size         = local.desired.caseworker
  min_size         = 0
  desired_capacity = local.desired.caseworker
  lifecycle {
    ignore_changes = [
      force_delete,
      tag,
      health_check_grace_period,
      launch_configuration,
      termination_policies,
      wait_for_capacity_timeout,
    ]
  }
}

resource "aws_autoscaling_group" "caseworker_api" {
  provider         = aws.old_refunds_production
  name             = "caseworker-api-${var.stack}"
  max_size         = local.desired.caseworker
  min_size         = 0
  desired_capacity = local.desired.caseworker
  lifecycle {
    ignore_changes = [
      force_delete,
      tag,
      health_check_grace_period,
      launch_configuration,
      termination_policies,
      wait_for_capacity_timeout,
    ]
  }
}
