module "daytime_non_prod" {
  source           = "./modules/ecs_scheduled_scaling"
  count            = local.account_name == "production" ? 0 : 1
  name             = "daytime_non_prod"
  ecs_cluster_name = aws_ecs_cluster.lpa_refunds.name
  scale_down_time  = "cron(00 19 ? * * *)"
  scale_up_time    = "cron(00 08 ? * * *)"
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

module "daytime_prod" {
  source           = "./modules/ecs_scheduled_scaling"
  count            = local.account_name == "production" ? 1 : 0
  name             = "daytime_prod"
  ecs_cluster_name = aws_ecs_cluster.lpa_refunds.name
  scale_down_time  = "cron(30 22 ? * MON-SAT *)"
  scale_up_time    = "cron(30 05 ? * MON-SAT *)"
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

