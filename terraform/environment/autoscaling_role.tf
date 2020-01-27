resource "aws_iam_role" "ecs_autoscaling" {
  name               = "${local.environment}-autoscaling-role"
  assume_role_policy = data.aws_iam_policy_document.ecs_assume_policy.json
  tags               = local.default_tags
}

resource "aws_iam_role_policy" "ecs_autoscaling_permissions_role" {
  name   = "${local.environment}-AppAutoscalingPermissions"
  policy = data.aws_iam_policy_document.ecs_autoscaling_permissions_role.json
  role   = aws_iam_role.ecs_autoscaling.id
}

data "aws_iam_policy_document" "ecs_autoscaling_permissions_role" {
  statement {
    sid = "ECSAutoScalingPolicy"

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
