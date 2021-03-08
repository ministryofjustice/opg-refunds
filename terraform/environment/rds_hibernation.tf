# for more info see
# https://github.com/diodonfrost/terraform-aws-lambda-scheduler-stop-start

module "start_rds_instances" {
  source                         = "diodonfrost/lambda-scheduler-stop-start/aws"
  name                           = "rds_scale_up"
  cloudwatch_schedule_expression = local.account.scale_up_schedule
  schedule_action                = "start"
  rds_schedule                   = "true"
  tags                           = merge( local.default_tags, local.shared_component_tag)
  resources_tag = {
    key   = "scheduled_shutdown"
    value = "true"
  }
}

module "stop_rds_instances" {
  source                         = "diodonfrost/lambda-scheduler-stop-start/aws"
  name                           = "rds_scale_down"
  cloudwatch_schedule_expression = local.account.scale_down_schedule
  schedule_action                = "stop"
  rds_schedule                   = "true"
  tags                           = merge( local.default_tags, local.shared_component_tag)
  resources_tag = {
    key   = "scheduled_shutdown"
    value = "true"
  }
}
