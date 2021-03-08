resource "aws_appautoscaling_target" "public_front" {
  max_capacity       = local.account.public_front_autoscaling_maximum
  min_capacity       = 1
  resource_id        = "service/${aws_ecs_cluster.lpa_refunds.name}/${aws_ecs_service.public_front.name}"
  role_arn           = data.aws_iam_role.ecs_autoscaling_service_role.arn
  scalable_dimension = "ecs:service:DesiredCount"
  service_namespace  = "ecs"
}

resource "aws_appautoscaling_policy" "cpu_track_metric" {
  name               = "${local.environment}-public-front-cpu-target-tracking"
  policy_type        = "TargetTrackingScaling"
  resource_id        = aws_appautoscaling_target.public_front.resource_id
  scalable_dimension = aws_appautoscaling_target.public_front.scalable_dimension
  service_namespace  = aws_appautoscaling_target.public_front.service_namespace

  target_tracking_scaling_policy_configuration {
    target_value       = local.account.public_front_autoscaling_metric_track_cpu
    scale_in_cooldown  = 60
    scale_out_cooldown = 60

    predefined_metric_specification {
      predefined_metric_type = "ECSServiceAverageCPUUtilization"
    }
  }
}

resource "aws_appautoscaling_policy" "memory_track_metric" {
  name               = "${local.environment}-public-front-memory-target-tracking"
  policy_type        = "TargetTrackingScaling"
  resource_id        = aws_appautoscaling_target.public_front.resource_id
  scalable_dimension = aws_appautoscaling_target.public_front.scalable_dimension
  service_namespace  = aws_appautoscaling_target.public_front.service_namespace

  target_tracking_scaling_policy_configuration {
    target_value       = local.account.public_front_autoscaling_metric_track_memory
    scale_in_cooldown  = 60
    scale_out_cooldown = 60

    predefined_metric_specification {
      predefined_metric_type = "ECSServiceAverageMemoryUtilization"
    }
  }
}

resource "aws_cloudwatch_metric_alarm" "public_front_max_scaling" {
  alarm_name                = "${local.environment}_public_front_max_scaling_reached"
  comparison_operator       = "GreaterThanOrEqualToThreshold"
  evaluation_periods        = "2"
  metric_name               = "RunningTaskCount"
  namespace                 = "ECS/ContainerInsights"
  period                    = "30"
  statistic                 = "Average"
  threshold                 = local.account.public_front_autoscaling_maximum
  alarm_description         = "This metric monitors ecs running task count for the public-front service"
  insufficient_data_actions = []
  tags                      = merge (local.default_tags, local.shared_component_tag)
  dimensions = {
    ServiceName = "public-front"
    ClusterName = "${local.environment}-lpa-refunds"
  }
}
