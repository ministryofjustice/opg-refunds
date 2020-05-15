//----------------------------------
// front ECS Service level config

resource "aws_ecs_service" "caseworker_front" {
  name            = "caseworker-front"
  cluster         = aws_ecs_cluster.lpa_refunds.id
  task_definition = aws_ecs_task_definition.caseworker_front.arn
  desired_count   = 2
  launch_type     = "FARGATE"

  network_configuration {
    security_groups = [
      aws_security_group.caseworker_front_ecs_service.id,
      aws_security_group.caseworker_api_ecs_service.id,
    ]
    subnets          = data.aws_subnet_ids.private.ids
    assign_public_ip = false
  }

  load_balancer {
    target_group_arn = aws_lb_target_group.caseworker_front.arn
    container_name   = "web"
    container_port   = 80
  }

  tags = local.default_tags
}

//----------------------------------
// The service's Security Groups

resource "aws_security_group" "caseworker_front_ecs_service" {
  name_prefix = "${local.environment}-caseworker-front-ecs-service"
  description = "caseworker front access"
  vpc_id      = data.aws_vpc.default.id
  tags        = local.default_tags
}

// 80 in from the ELB
resource "aws_security_group_rule" "caseworker_front_ecs_service_ingress" {
  type                     = "ingress"
  from_port                = 80
  to_port                  = 80
  protocol                 = "tcp"
  security_group_id        = aws_security_group.caseworker_front_ecs_service.id
  source_security_group_id = aws_security_group.caseworker_front_loadbalancer.id
}

// Anything out
resource "aws_security_group_rule" "caseworker_front_ecs_service_egress" {
  type              = "egress"
  from_port         = 0
  to_port           = 0
  protocol          = "-1"
  cidr_blocks       = ["0.0.0.0/0"]
  security_group_id = aws_security_group.caseworker_front_ecs_service.id
}

//--------------------------------------
// caseworker_front ECS Service Task level config

resource "aws_ecs_task_definition" "caseworker_front" {
  family                   = "${local.environment}-caseworker-front"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 2048
  memory                   = 4096
  container_definitions    = "[${local.caseworker_front_web}, ${local.caseworker_front_app}]"
  task_role_arn            = aws_iam_role.caseworker_front_task_role.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = local.default_tags
}

//----------------
// Permissions

resource "aws_iam_role" "caseworker_front_task_role" {
  name               = "${local.environment}-caseworker-front-task-role"
  assume_role_policy = data.aws_iam_policy_document.ecs_assume_policy.json
  tags               = local.default_tags
}

resource "aws_iam_role_policy_attachment" "caseworker_front_vpc_endpoint_access" {
  policy_arn = data.aws_iam_policy.restrict_to_vpc_endpoints.arn
  role       = aws_iam_role.caseworker_front_task_role.id
}

resource "aws_iam_role_policy" "caseworker_front_permissions_role" {
  name   = "${local.environment}-caseworker-frontApplicationPermissions"
  policy = data.aws_iam_policy_document.caseworker_front_permissions_role.json
  role   = aws_iam_role.caseworker_front_task_role.id
}

/*
  Defines permissions that the application running within the task has.
*/
data "aws_iam_policy_document" "caseworker_front_permissions_role" {
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
      aws_dynamodb_table.sessions_caseworker_front.arn,
    ]
  }
  statement {
    sid    = "bankencrypt"
    effect = "Allow"
    actions = [
      "kms:Encrypt",
    ]
    resources = [
      data.aws_kms_alias.bank_encrypt_decrypt.target_key_arn,
    ]
  }
}

data "aws_ecr_repository" "lpa_refunds_caseworker_front_web" {
  provider = aws.management
  name     = "lpa-refunds/caseworker_front_web"
}

data "aws_ecr_repository" "lpa_refunds_caseworker_front_app" {
  provider = aws.management
  name     = "lpa-refunds/caseworker_front_app"
}

//-----------------------------------------------
// caseworker_front ECS Service Task Container level config

locals {
  caseworker_front_web = <<EOF
  {
    "cpu": 1,
    "essential": true,
    "image": "${data.aws_ecr_repository.lpa_refunds_caseworker_front_web.repository_url}:${var.container_version}",
    "mountPoints": [],
    "name": "web",
    "portMappings": [
        {
            "containerPort": 80,
            "hostPort": 80,
            "protocol": "tcp"
        }
    ],
    "volumesFrom": [],
    "logConfiguration": {
        "logDriver": "awslogs",
        "options": {
            "awslogs-group": "${data.aws_cloudwatch_log_group.lpa_refunds.name}",
            "awslogs-region": "eu-west-1",
            "awslogs-stream-prefix": "${local.environment}.caseworker-front-web.lpa-refunds"
        }
    },
    "environment": [
    {"name": "APP_HOST", "value": "127.0.0.1"},
    {"name": "APP_PORT", "value": "9000"},
    {"name": "TIMEOUT", "value": "60"},
    {"name": "CONTAINER_VERSION", "value": "${var.container_version}"}
    ]
  }
  EOF

  caseworker_front_app = <<EOF
  {
    "cpu": 1,
    "essential": true,
    "image": "${data.aws_ecr_repository.lpa_refunds_caseworker_front_app.repository_url}:${var.container_version}",
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
            "awslogs-stream-prefix": "${local.environment}.caseworker-front-app.lpa-refunds"
        }
    },
    "secrets": [
      { "name" : "OPG_REFUNDS_AD_LINK_SIGNATURE_KEY", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_ad_link_signature_key.name}" },
      { "name" : "OPG_REFUNDS_NOTIFY_API_KEY", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_notify_api_key.name}" }
  ],
    "environment": [
      { "name" : "OPG_LPA_STACK_NAME", "value": "${local.environment}" },
      { "name" : "OPG_LPA_STACK_ENVIRONMENT", "value": "${local.environment}" },
      { "name" : "OPG_REFUNDS_STACK_TYPE", "value": "${local.account.opg_refunds_stack_type}" },
      { "name" : "OPG_REFUNDS_CASEWORKER_FRONT_SESSION_DYNAMODB_TABLE", "value": "${aws_dynamodb_table.sessions_caseworker_front.name}" },
      { "name" : "API_URL", "value": "http://${local.caseworker_api_service_fqdn}" },
      { "name" : "OPG_REFUNDS_PUBLIC_FRONT_HOSTNAME", "value": "${aws_route53_record.public_front.fqdn}" }
    ]
  }
  EOF
}
