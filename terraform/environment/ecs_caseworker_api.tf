//----------------------------------
// caseworker_api ECS Service level config

resource "aws_ecs_service" "caseworker_api" {
  name            = "caseworker_api"
  cluster         = aws_ecs_cluster.lpa_refunds.id
  task_definition = aws_ecs_task_definition.caseworker_api.arn
  desired_count   = 2
  launch_type     = "FARGATE"

  network_configuration {
    security_groups = [
      aws_security_group.caseworker_api_ecs_service.id,
      aws_security_group.caseworker_rds_cluster_client.id,
    ]
    subnets          = data.aws_subnet_ids.private.ids
    assign_public_ip = false
  }

  service_registries {
    registry_arn = aws_service_discovery_service.caseworker_api.arn
  }
  tags = merge(local.default_tags, local.caseworker_component_tag)
}

//-----------------------------------------------
// caseworker_api service discovery

resource "aws_service_discovery_service" "caseworker_api" {
  name = "caseworker_api"

  dns_config {
    namespace_id = aws_service_discovery_private_dns_namespace.internal.id

    dns_records {
      ttl  = 10
      type = "A"
    }

    routing_policy = "MULTIVALUE"
  }

  health_check_custom_config {
    failure_threshold = 1
  }
}

//
locals {
  caseworker_api_service_fqdn = "${aws_service_discovery_service.caseworker_api.name}.${aws_service_discovery_private_dns_namespace.internal.name}"
}

//----------------------------------
// The caseworker_api service's Security Groups

resource "aws_security_group" "caseworker_api_ecs_service" {
  name_prefix = "${local.environment}-caseworker_api-ecs-service"
  description = "caseworker api access"
  vpc_id      = data.aws_vpc.default.id
  tags        = merge(local.default_tags, local.caseworker_component_tag)
}

//----------------------------------
// 80 in from front ECS service

resource "aws_security_group_rule" "caseworker_api_ecs_service_front_ingress" {
  type                     = "ingress"
  from_port                = 80
  to_port                  = 80
  protocol                 = "tcp"
  security_group_id        = aws_security_group.caseworker_api_ecs_service.id
  source_security_group_id = aws_security_group.caseworker_front_ecs_service.id
}

//----------------------------------
// Anything out
resource "aws_security_group_rule" "caseworker_api_ecs_service_egress" {
  type              = "egress"
  from_port         = 0
  to_port           = 0
  protocol          = "-1"
  cidr_blocks       = ["0.0.0.0/0"]
  security_group_id = aws_security_group.caseworker_api_ecs_service.id
}

//--------------------------------------
// caseworker_api ECS Service Task level config

resource "aws_ecs_task_definition" "caseworker_api" {
  family                   = "${local.environment}-caseworker_api"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 2048
  memory                   = 4096
  container_definitions    = "[${local.caseworker_api_web}, ${local.caseworker_api_app}]"
  task_role_arn            = aws_iam_role.caseworker_api_task_role.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = merge(local.default_tags, local.caseworker_component_tag)
}


//----------------
// Permissions

resource "aws_iam_role" "caseworker_api_task_role" {
  name               = "${local.environment}-caseworker_api-task-role"
  assume_role_policy = data.aws_iam_policy_document.ecs_assume_policy.json
  tags               = merge(local.default_tags, local.caseworker_component_tag)
}

resource "aws_iam_role_policy_attachment" "caseworker_api_vpc_endpoint_access" {
  policy_arn = data.aws_iam_policy.restrict_to_vpc_endpoints.arn
  role       = aws_iam_role.caseworker_api_task_role.id
}

resource "aws_iam_role_policy" "caseworker_api_permissions_role" {
  name   = "${local.environment}-caseworker_apiApplicationPermissions"
  policy = data.aws_iam_policy_document.caseworker_api_permissions_role.json
  role   = aws_iam_role.caseworker_api_task_role.id
}

/*
  Defines permissions that the application running within the task has.
*/
data "aws_iam_policy_document" "caseworker_api_permissions_role" {
  statement {
    sid    = "bankdecrypt"
    effect = "Allow"
    actions = [
      "kms:Decrypt",
    ]
    resources = [
      data.aws_kms_alias.bank_encrypt_decrypt.target_key_arn,
    ]
  }
}


data "aws_ecr_repository" "caseworker_api_web" {
  provider = aws.management
  name     = "lpa-refunds/caseworker_api_web"
}

data "aws_ecr_repository" "caseworker_api_app" {
  provider = aws.management
  name     = "lpa-refunds/caseworker_api_app"
}

//-----------------------------------------------
// api ECS Service Task Container level config

locals {
  caseworker_api_web = <<EOF
  {
    "cpu": 1,
    "essential": true,
    "image": "${data.aws_ecr_repository.caseworker_api_web.repository_url}:${var.container_version}",
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
            "awslogs-stream-prefix": "${local.environment}.caseworker-api-web.lpa-refunds"
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

  caseworker_api_app = <<EOF
  {
    "cpu": 1,
    "essential": true,
    "image": "${data.aws_ecr_repository.caseworker_api_app.repository_url}:${var.container_version}",
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
            "awslogs-stream-prefix": "${local.environment}.caseworker-api-app.lpa-refunds"
        }
    },
    "secrets": [
      { "name" : "OPG_REFUNDS_BANK_HASH_SALT", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_bank_hash_salt.name}" },
      { "name" : "OPG_REFUNDS_DB_APPLICATIONS_FULL_PASSWORD", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_db_applications_full_password.name}" },
      { "name" : "OPG_REFUNDS_DB_CASES_FULL_PASSWORD", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_db_cases_full_password.name}" },
      { "name" : "OPG_REFUNDS_SSCL_ENTITY", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_sscl_entity.name}" },
      { "name" : "OPG_REFUNDS_SSCL_COST_CENTRE", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_sscl_cost_centre.name}" },
      { "name" : "OPG_REFUNDS_SSCL_ACCOUNT", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_sscl_account.name}" },
      { "name" : "OPG_REFUNDS_SSCL_ANALYSIS", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_sscl_analysis.name}" },
      { "name" : "OPG_REFUNDS_NOTIFY_API_KEY", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_notify_api_key.name}" }

    ],
    "environment": [
      { "name" : "POSTGRES_USER", "value": "${local.rds_master_username}" },
      { "name" : "OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME", "value": "${aws_rds_cluster.applications.endpoint}" },
      { "name" : "OPG_REFUNDS_DB_APPLICATIONS_PORT", "value": "5432" },
      { "name" : "OPG_REFUNDS_DB_APPLICATIONS_NAME", "value": "applications" },
      { "name" : "OPG_REFUNDS_DB_APPLICATIONS_FULL_USERNAME", "value": "applications_full" },
      { "name" : "OPG_REFUNDS_DB_CASES_HOSTNAME", "value": "${aws_rds_cluster.caseworker.endpoint}" },
      { "name" : "OPG_REFUNDS_DB_CASES_PORT", "value": "5432" },
      { "name" : "OPG_REFUNDS_DB_CASES_NAME", "value": "cases" },
      { "name" : "OPG_REFUNDS_DB_CASES_FULL_USERNAME", "value": "cases_full" },
      { "name" : "OPG_REFUNDS_DELETE_AFTER_HISTORICAL_REFUND_DATES", "value": "${local.account.days_to_wait_before_expiry}"}
    ]
    }
  EOF
}
