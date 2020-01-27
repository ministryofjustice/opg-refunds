resource "aws_appautoscaling_target" "public_front" {
  max_capacity       = 5
  min_capacity       = 1
  resource_id        = "service/${aws_ecs_cluster.lpa_refunds.name}/${aws_ecs_service.public_front.name}"
  role_arn           = aws_iam_role.ecs_autoscaling.arn
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
    target_value       = "1"
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
    target_value       = "1"
    scale_in_cooldown  = 60
    scale_out_cooldown = 60

    predefined_metric_specification {
      predefined_metric_type = "ECSServiceAverageMemoryUtilization"
    }
  }
}
