module "daytime" {
  source           = "./modules/ecs_scheduled_scaling"
  count            = local.account_name == "development" ? 1 : 0
  name             = "daytime"
  ecs_cluster_name = aws_ecs_cluster.lpa_refunds.name
  scale_down_time  = "cron(00 19 ? * * *)"
  scale_up_time    = "cron(00 08 ? * * *)"
  service_config = {
    tostring(aws_ecs_service.caseworker_api.name) = {
      scale_down_to = 0
      scale_up_to   = aws_ecs_service.caseworker_api.desired_count
    }
    tostring(aws_ecs_service.ingestion.name) = {
      scale_down_to = 0
      scale_up_to   = aws_ecs_service.ingestion.desired_count
    }
    tostring(aws_ecs_service.caseworker_front.name) = {
      scale_down_to = 0
      scale_up_to   = aws_ecs_service.caseworker_front.desired_count
    }
    tostring(aws_ecs_service.public_front.name) = {
      scale_down_to = 0
      scale_up_to   = local.account.public_front_autoscaling_maximum
    }
  }
}
