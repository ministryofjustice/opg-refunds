//----------------------------------
// front ECS Service level config

resource "aws_ecs_service" "public_front" {
  name            = "public-front"
  cluster         = aws_ecs_cluster.lpa_refunds.id
  task_definition = aws_ecs_task_definition.public_front.arn
  desired_count   = 5
  launch_type     = "FARGATE"

  network_configuration {
    security_groups = [
      aws_security_group.public_front_ecs_service.id,
      aws_security_group.applications_rds_cluster_client.id,
    ]
    subnets          = data.aws_subnet_ids.private.ids
    assign_public_ip = false
  }

  load_balancer {
    target_group_arn = aws_lb_target_group.public_front.arn
    container_name   = "web"
    container_port   = 80
  }

  depends_on = [aws_rds_cluster.applications, aws_lb.public_front, aws_iam_role.public_front_task_role, aws_iam_role.execution_role]
  tags       = local.default_tags
  lifecycle {
    ignore_changes = ["desired_count"]
  }
}

//----------------------------------
// The service's Security Groups

resource "aws_security_group" "public_front_ecs_service" {
  name_prefix = "${local.environment}-public-front-ecs-service"
  description = "public front ecs access"
  vpc_id      = data.aws_vpc.default.id
  tags        = local.default_tags
}

// 80 in from the ELB
resource "aws_security_group_rule" "public_front_ecs_service_ingress" {
  type                     = "ingress"
  from_port                = 80
  to_port                  = 80
  protocol                 = "tcp"
  security_group_id        = aws_security_group.public_front_ecs_service.id
  source_security_group_id = aws_security_group.public_front_loadbalancer.id
}

// Anything out
resource "aws_security_group_rule" "public_front_ecs_service_egress" {
  type              = "egress"
  from_port         = 0
  to_port           = 0
  protocol          = "-1"
  cidr_blocks       = ["0.0.0.0/0"]
  security_group_id = aws_security_group.public_front_ecs_service.id
}

//--------------------------------------
// public_front ECS Service Task level config

resource "aws_ecs_task_definition" "public_front" {
  family                   = "${local.environment}-public-front"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 2048
  memory                   = 4096
  container_definitions    = "[${local.public_front_web}, ${local.public_front_app}]"
  task_role_arn            = aws_iam_role.public_front_task_role.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = local.default_tags
}

//----------------
// Permissions

resource "aws_iam_role" "public_front_task_role" {
  name               = "${local.environment}-public-front-task-role"
  assume_role_policy = data.aws_iam_policy_document.ecs_assume_policy.json
  tags               = local.default_tags
}

resource "aws_iam_role_policy_attachment" "public_front_vpc_endpoint_access" {
  policy_arn = data.aws_iam_policy.restrict_to_vpc_endpoints.arn
  role       = aws_iam_role.public_front_task_role.id
}

resource "aws_iam_role_policy" "public_front_permissions_role" {
  name   = "${local.environment}-public-frontApplicationPermissions"
  policy = data.aws_iam_policy_document.public_front_permissions_role.json
  role   = aws_iam_role.public_front_task_role.id
}

/*
  Defines permissions that the application running within the task has.
*/
data "aws_iam_policy_document" "public_front_permissions_role" {
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
      aws_dynamodb_table.sessions_public_front.arn,
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

data "aws_ecr_repository" "lpa_refunds_public_front_web" {
  provider = aws.management
  name     = "lpa-refunds/public_front_web"
}

data "aws_ecr_repository" "lpa_refunds_public_front_app" {
  provider = aws.management
  name     = "lpa-refunds/public_front_app"
}

//-----------------------------------------------
// public_front ECS Service Task Container level config

locals {
  public_front_web = <<EOF
  {
    "cpu": 1,
    "essential": true,
    "image": "${data.aws_ecr_repository.lpa_refunds_public_front_web.repository_url}:${var.container_version}",
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
            "awslogs-stream-prefix": "public-front-web.lpa-refunds"
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

  public_front_app = <<EOF
  {
    "cpu": 1,
    "essential": true,
    "image": "${data.aws_ecr_repository.lpa_refunds_public_front_app.repository_url}:${var.container_version}",
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
            "awslogs-stream-prefix": "public-front-app.lpa-refunds"
        }
    },
    "secrets": [
      { "name" : "OPG_REFUNDS_DB_APPLICATIONS_WRITE_PASSWORD", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_db_applications_write_password.name}" },
      { "name" : "OPG_REFUNDS_BANK_HASH_SALT", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_bank_hash_salt.name}" },
      { "name" : "OPG_REFUNDS_PUBLIC_FRONT_BETA_LINK_SIGNATURE_KEY", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_public_front_beta_link_signature_key.name}" },
      { "name" : "OPG_REFUNDS_AD_LINK_SIGNATURE_KEY", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_ad_link_signature_key.name}" },
      { "name" : "OPG_REFUNDS_NOTIFY_API_KEY", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_notify_api_key.name}" }
  ],
    "environment": [
      { "name" : "OPG_LPA_STACK_NAME", "value": "${local.environment}" },
      { "name" : "OPG_LPA_STACK_ENVIRONMENT", "value": "dev" },
      { "name" : "OPG_DOCKER_TAG", "value": "${var.container_version}"},
      { "name" : "OPG_REFUNDS_PUBLIC_FRONT_SESSION_DYNAMODB_TABLE", "value": "${aws_dynamodb_table.sessions_public_front.name}" },
      { "name" : "OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME", "value": "${aws_rds_cluster.applications.endpoint}" },
      { "name" : "OPG_REFUNDS_DB_APPLICATIONS_PORT", "value": "5432" },
      { "name" : "OPG_REFUNDS_DB_APPLICATIONS_NAME", "value": "applications" },
      { "name" : "OPG_REFUNDS_DB_APPLICATIONS_WRITE_USERNAME", "value": "applications" },
      { "name" : "OPG_REFUNDS_PUBLIC_FRONT_KMS_ENCRYPT_KEY_ALIAS", "value": "${data.aws_kms_alias.bank_encrypt_decrypt.name}" },
      { "name" : "OPG_REFUNDS_PUBLIC_FRONT_GOOGLE_ANALYTICS_TRACKING_ID", "value": "UA-105655306-1" },
      { "name" : "OPG_REFUNDS_PUBLIC_FRONT_GOOGLE_ANALYTICS_TRACKING_GOVID", "value": "UA-145652997-1" },
      { "name" : "OPG_REFUNDS_STACK_TYPE", "value": "testing" }
    ]
  }
  EOF
}
