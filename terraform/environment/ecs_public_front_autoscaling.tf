# IAM
resource "aws_iam_role" "public_front_autoscaling" {
  name               = "${local.environment}-public-front-autoscaling-role"
  assume_role_policy = data.aws_iam_policy_document.ecs_assume_policy.json
  tags               = local.default_tags
}

resource "aws_iam_role_policy" "public_front_autoscaling_permissions_role" {
  name   = "${local.environment}-public-frontAppAutoscalingPermissions"
  policy = data.aws_iam_policy_document.public_front_autoscaling_permissions_role.json
  role   = aws_iam_role.public_front_autoscaling.id
}

data "aws_iam_policy_document" "public_front_autoscaling_permissions_role" {
  statement {
    sid = "DynamoDBAccess"

    effect = "Allow"

    actions = [
      "application-autoscaling:*",
      "ecs:DescribeServices",
      "ecs:UpdateService",
      "cloudwatch:DescribeAlarms",
      "cloudwatch:PutMetricAlarm",
      "cloudwatch:DeleteAlarms",
      "cloudwatch:DescribeAlarmHistory",
      "cloudwatch:DescribeAlarms",
      "cloudwatch:DescribeAlarmsForMetric",
      "cloudwatch:GetMetricStatistics",
      "cloudwatch:ListMetrics",
      "cloudwatch:PutMetricAlarm",
      "cloudwatch:DisableAlarmActions",
      "cloudwatch:EnableAlarmActions",
      "iam:CreateServiceLinkedRole",
      "sns:CreateTopic",
      "sns:Subscribe",
      "sns:Get*",
      "sns:List*",
    ]
    # TODO: Update resources to limit access to only those needed for autoscaling public front
    resources = [
      "*",
    ]
  }
}

# Application Auto Scaling
resource "aws_appautoscaling_target" "public_front" {
  max_capacity       = 5
  min_capacity       = 1
  resource_id        = "service/${aws_ecs_cluster.lpa_refunds.name}/${aws_ecs_service.public_front.name}"
  role_arn           = aws_iam_role.public_front_autoscaling.arn
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
      predefined_metric_type = "CpuUtilized"
    }
  }
}

# CloudWatch
