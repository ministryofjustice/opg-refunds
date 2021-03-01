module "daytime" {
  source           = "./modules/ecs_scheduled_scaling"
  name             = "daytime"
  ecs_cluster_name = aws_ecs_cluster.lpa_refunds.name
  scale_down_time  = local.account.scale_down_schedule
  scale_up_time    = local.account.scale_up_schedule
  service_config = {
    tostring(aws_ecs_service.caseworker_api.name) = {
      scale_down_to = 0
      scale_up_to   = local.account.caseworker_api_autoscaling_maximum
    }
    tostring(aws_ecs_service.ingestion.name) = {
      scale_down_to = 0
      scale_up_to   = local.account.ingestion_autoscaling_maximum
    }
    tostring(aws_ecs_service.caseworker_front.name) = {
      scale_down_to = 0
      scale_up_to   = local.account.caseworker_front_autoscaling_maximum
    }
    tostring(aws_ecs_service.public_front.name) = {
      scale_down_to = 0
      scale_up_to   = local.account.public_front_autoscaling_maximum
    }
  }
}

