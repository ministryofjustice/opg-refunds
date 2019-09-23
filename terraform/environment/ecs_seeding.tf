//----------------------------------
// seeding ECS Service level config

resource "aws_ecs_service" "seeding" {
  name            = "seeding"
  cluster         = aws_ecs_cluster.lpa_refunds.id
  task_definition = aws_ecs_task_definition.seeding.arn
  desired_count   = 0
  launch_type     = "FARGATE"

  network_configuration {
    security_groups = [
      aws_security_group.seeding_ecs_service.id,
      aws_security_group.applications_rds_cluster_client.id,
      aws_security_group.caseworker_rds_cluster_client.id,
    ]
    subnets          = data.aws_subnet_ids.private.ids
    assign_public_ip = false
  }

  depends_on = [
    aws_rds_cluster.applications,
    aws_rds_cluster.caseworker,
    aws_iam_role.seeding_task_role,
    aws_iam_role.execution_role
  ]
}

//----------------------------------
// The service's Security Groups

resource "aws_security_group" "seeding_ecs_service" {
  name_prefix = "${local.environment}-seeding-ecs-service"
  description = "seeding access"
  vpc_id      = data.aws_vpc.default.id
  tags        = local.default_tags
}

//----------------------------------
// Anything out
resource "aws_security_group_rule" "seeding_ecs_service_egress" {
  type              = "egress"
  from_port         = 0
  to_port           = 0
  protocol          = "-1"
  cidr_blocks       = ["0.0.0.0/0"]
  security_group_id = "${aws_security_group.seeding_ecs_service.id}"
}

//--------------------------------------
// seeding ECS Service Task level config

resource "aws_ecs_task_definition" "seeding" {
  family                   = "${local.environment}-seeding"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 2048
  memory                   = 4096
  container_definitions    = "[${local.seeding_app}]"
  task_role_arn            = aws_iam_role.seeding_task_role.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = local.default_tags
}

//----------------
// Permissions

resource "aws_iam_role" "seeding_task_role" {
  name               = "${local.environment}-seeding-task-role"
  assume_role_policy = data.aws_iam_policy_document.ecs_assume_policy.json
  tags               = local.default_tags
}

resource "aws_iam_role_policy_attachment" "seeding_vpc_endpoint_access" {
  policy_arn = data.aws_iam_policy.restrict_to_vpc_endpoints.arn
  role       = aws_iam_role.seeding_task_role.id
}

resource "aws_iam_role_policy" "seeding_permissions_role" {
  name   = "${local.environment}-seedingApplicationPermissions"
  policy = data.aws_iam_policy_document.seeding_permissions_role.json
  role   = aws_iam_role.seeding_task_role.id
}

/*
  Defines permissions that the application running within the task has.
*/
data "aws_iam_policy_document" "seeding_permissions_role" {
  statement {
    sid = "DynamoDBAccess"

    effect = "Allow"

    actions = [
      "dynamodb:BatchGetItem",
      "dynamodb:BatchWriteItem",
      "dynamodb:DeleteItem",
      "dynamodb:DescribeStream",
      "dynamodb:DescribeTable",
      "dynamodb:GetItem",
      "dynamodb:GetRecords",
      "dynamodb:GetShardIterator",
      "dynamodb:ListStreams",
      "dynamodb:ListTables",
      "dynamodb:PutItem",
      "dynamodb:Query",
      "dynamodb:Scan",
      "dynamodb:UpdateItem",
      "dynamodb:UpdateTable",
    ]

    resources = [
      aws_dynamodb_table.cronlock.arn,
    ]
  }
}

data "aws_ecr_repository" "caseworker_api_seeding" {
  provider = "aws.management"
  name     = "lpa-refunds/caseworker_api_seeding"
}

//-----------------------------------------------
// seeding ECS Service Task Container level config

locals {
  seeding_app = <<EOF
  {
    "cpu": 1,
    "essential": true,
    "image": "${data.aws_ecr_repository.caseworker_api_seeding.repository_url}:${var.container_version}",
    "mountPoints": [],
    "name": "app",
    "portMappings": [
        {
            "containerPort": 9000,
            "hostPort": 9000,
            "protocol": "tcp"
        }
    ],
    "volumesFrom": [],
    "logConfiguration": {
        "logDriver": "awslogs",
        "options": {
            "awslogs-group": "${data.aws_cloudwatch_log_group.lpa_refunds.name}",
            "awslogs-region": "eu-west-1",
            "awslogs-stream-prefix": "seeding-app.online-lpa"
        }
    },
    "secrets": [
      { "name" : "POSTGRES_PASSWORD", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.postgres_password.name}" },
      { "name" : "PGPASSWORD", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.postgres_password.name}" }
    ],
    "environment": [
      { "name" : "POSTGRES_USER", "value": "${local.rds_master_username}" },
      { "name" : "OPG_REFUNDS_DB_CASES_HOSTNAME", "value": "${aws_rds_cluster.caseworker.endpoint}" },
      { "name" : "OPG_REFUNDS_DB_CASES_PORT", "value": "5432" },
      { "name" : "OPG_REFUNDS_DB_SIRIUS_HOSTNAME", "value": "${aws_rds_cluster.caseworker.endpoint}" },
      { "name" : "OPG_REFUNDS_DB_SIRIUS_PORT", "value": "5432" },
      { "name" : "OPG_REFUNDS_DB_MERIS_HOSTNAME", "value": "${aws_rds_cluster.caseworker.endpoint}" },
      { "name" : "OPG_REFUNDS_DB_MERIS_PORT", "value": "5432" },
      { "name" : "OPG_REFUNDS_DB_FINANCE_HOSTNAME", "value": "${aws_rds_cluster.caseworker.endpoint}" },
      { "name" : "OPG_REFUNDS_DB_FINANCE_PORT", "value": "5432" },
      { "name" : "OPG_REFUNDS_CRONLOCK_DYNAMODB_TABLE", "value": "${aws_dynamodb_table.cronlock.name}" }
    ]
  }
  EOF
}
