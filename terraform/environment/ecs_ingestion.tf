//----------------------------------
// ingestion ECS Service level config

resource "aws_ecs_service" "ingestion" {
  name            = "ingestion"
  cluster         = aws_ecs_cluster.lpa_refunds.id
  task_definition = aws_ecs_task_definition.ingestion.arn
  desired_count   = 1
  launch_type     = "FARGATE"

  network_configuration {
    security_groups = [
      aws_security_group.ingestion_ecs_service.id,
      aws_security_group.applications_rds_cluster_client.id,
      aws_security_group.caseworker_rds_cluster_client.id,
    ]
    subnets          = data.aws_subnet_ids.private.ids
    assign_public_ip = false
  }

  depends_on = [aws_rds_cluster.applications, aws_iam_role.ingestion_task_role, aws_iam_role.execution_role]
}

//----------------------------------
// The service's Security Groups

resource "aws_security_group" "ingestion_ecs_service" {
  name_prefix = "${local.environment}-ingestion-ecs-service"
  vpc_id      = data.aws_vpc.default.id
  tags        = local.default_tags
}

//----------------------------------
// Anything out
resource "aws_security_group_rule" "ingestion_ecs_service_egress" {
  type              = "egress"
  from_port         = 0
  to_port           = 0
  protocol          = "-1"
  cidr_blocks       = ["0.0.0.0/0"]
  security_group_id = "${aws_security_group.ingestion_ecs_service.id}"
}

//--------------------------------------
// ingestion ECS Service Task level config

resource "aws_ecs_task_definition" "ingestion" {
  family                   = "${local.environment}-ingestion"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 2048
  memory                   = 4096
  container_definitions    = "[${local.ingestion_app}]"
  task_role_arn            = aws_iam_role.ingestion_task_role.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = local.default_tags
}

//----------------
// Permissions

resource "aws_iam_role" "ingestion_task_role" {
  name               = "${local.environment}-ingestion-task-role"
  assume_role_policy = data.aws_iam_policy_document.ecs_assume_policy.json
  tags               = local.default_tags
}

resource "aws_iam_role_policy" "ingestion_permissions_role" {
  name   = "${local.environment}-ingestionApplicationPermissions"
  policy = data.aws_iam_policy_document.ingestion_permissions_role.json
  role   = aws_iam_role.ingestion_task_role.id
}

/*
  Defines permissions that the application running within the task has.
*/
data "aws_iam_policy_document" "ingestion_permissions_role" {
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

data "aws_ecr_repository" "caseworker_api_ingestion" {
  provider = "aws.management"
  name     = "lpa-refunds/caseworker_api_ingestion"
}

//-----------------------------------------------
// ingestion ECS Service Task Container level config

locals {
  ingestion_app = <<EOF
  {
    "cpu": 1,
    "essential": true,
    "image": "${data.aws_ecr_repository.caseworker_api_ingestion.repository_url}:${var.container_version}",
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
            "awslogs-stream-prefix": "ingestion-app.online-lpa"
        }
    },
    "secrets": [
      { "name" : "POSTGRES_PASSWORD", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.postgres_password.name}" } ,
      { "name" : "PGPASSWORD", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.postgres_password.name}" } ,
      { "name" : "OPG_REFUNDS_DB_APPLICATIONS_WRITE_PASSWORD", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_db_applications_write_password.name}" },
      { "name" : "OPG_REFUNDS_DB_APPLICATIONS_FULL_PASSWORD", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_db_applications_full_password.name}" },
      { "name" : "OPG_REFUNDS_DB_APPLICATIONS_MIGRATION_PASSWORD", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_db_applications_migration_password.name}" },
      { "name" : "OPG_REFUNDS_DB_CASES_FULL_PASSWORD", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_db_cases_full_password.name}" },
      { "name" : "OPG_REFUNDS_DB_CASES_MIGRATION_PASSWORD", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_db_cases_migration_password.name}" },
      { "name" : "OPG_REFUNDS_DB_SIRIUS_FULL_PASSWORD", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_db_sirius_full_password.name}" },
      { "name" : "OPG_REFUNDS_DB_SIRIUS_MIGRATION_PASSWORD", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_db_sirius_migration_password.name}" },
      { "name" : "OPG_REFUNDS_DB_MERIS_FULL_PASSWORD", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_db_meris_full_password.name}" },
      { "name" : "OPG_REFUNDS_DB_MERIS_MIGRATION_PASSWORD", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_db_meris_migration_password.name}" },
      { "name" : "OPG_REFUNDS_DB_FINANCE_FULL_PASSWORD", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_db_finance_full_password.name}" },
      { "name" : "OPG_REFUNDS_DB_FINANCE_MIGRATION_PASSWORD", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_db_finance_migration_password.name}" },
      { "name" : "OPG_REFUNDS_CASEWORKER_ADMIN_USERNAME", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_caseworker_admin_username.name}" },
      { "name" : "OPG_REFUNDS_CASEWORKER_ADMIN_PASSWORD", "valueFrom": "/aws/reference/secretsmanager/${data.aws_secretsmanager_secret.opg_refunds_caseworker_admin_password.name}" }
    ],
    "environment": [
      { "name" : "POSTGRES_USER", "value": "${aws_rds_cluster.applications.master_username}" } ,
      { "name" : "OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME", "value": "${aws_rds_cluster.applications.endpoint}" },
      { "name" : "OPG_REFUNDS_DB_APPLICATIONS_PORT", "value": "5432" },
      { "name" : "OPG_REFUNDS_DB_APPLICATIONS_NAME", "value": "applications" },
      { "name" : "OPG_REFUNDS_DB_APPLICATIONS_WRITE_USERNAME", "value": "applications" },
      { "name" : "OPG_REFUNDS_DB_APPLICATIONS_FULL_USERNAME", "value": "applications_full" },
      { "name" : "OPG_REFUNDS_DB_APPLICATIONS_MIGRATION_USERNAME", "value": "applications_migration" },
      { "name" : "OPG_REFUNDS_DB_CASES_HOSTNAME", "value": "${aws_rds_cluster.caseworker.endpoint}" },
      { "name" : "OPG_REFUNDS_DB_CASES_PORT", "value": "5432" },
      { "name" : "OPG_REFUNDS_DB_CASES_NAME", "value": "cases" },
      { "name" : "OPG_REFUNDS_DB_CASES_FULL_USERNAME", "value": "cases_full" },
      { "name" : "OPG_REFUNDS_DB_CASES_MIGRATION_USERNAME", "value": "cases_migration" },
      { "name" : "OPG_REFUNDS_DB_SIRIUS_HOSTNAME", "value": "${aws_rds_cluster.caseworker.endpoint}" },
      { "name" : "OPG_REFUNDS_DB_SIRIUS_PORT", "value": "5432" },
      { "name" : "OPG_REFUNDS_DB_SIRIUS_NAME", "value": "sirius" },
      { "name" : "OPG_REFUNDS_DB_SIRIUS_FULL_USERNAME", "value": "sirius_full" },
      { "name" : "OPG_REFUNDS_DB_SIRIUS_MIGRATION_USERNAME", "value": "sirius_migration" },
      { "name" : "OPG_REFUNDS_DB_MERIS_HOSTNAME", "value": "${aws_rds_cluster.caseworker.endpoint}" },
      { "name" : "OPG_REFUNDS_DB_MERIS_PORT", "value": "5432" },
      { "name" : "OPG_REFUNDS_DB_MERIS_NAME", "value": "meris" },
      { "name" : "OPG_REFUNDS_DB_MERIS_FULL_USERNAME", "value": "meris_full" },
      { "name" : "OPG_REFUNDS_DB_MERIS_MIGRATION_USERNAME", "value": "meris_migration" },
      { "name" : "OPG_REFUNDS_DB_FINANCE_HOSTNAME", "value": "${aws_rds_cluster.caseworker.endpoint}" },
      { "name" : "OPG_REFUNDS_DB_FINANCE_PORT", "value": "5432" },
      { "name" : "OPG_REFUNDS_DB_FINANCE_NAME", "value": "finance" },
      { "name" : "OPG_REFUNDS_DB_FINANCE_FULL_USERNAME", "value": "finance_full" },
      { "name" : "OPG_REFUNDS_DB_FINANCE_MIGRATION_USERNAME", "value": "finance_migration" },
      { "name" : "OPG_REFUNDS_CASEWORKER_ADMIN_NAME", "value": "Admin User 01" },
      { "name" : "OPG_REFUNDS_CASEWORKER_INGESTION_ENABLED", "value": "true" },
      { "name" : "PHP_OPCACHE_VALIDATE_TIMESTAMPS", "value": "1" }
    ]
  }
  EOF
}
