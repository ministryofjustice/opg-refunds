resource "aws_rds_cluster" "applications" {
  cluster_identifier     = "applications-${local.environment}"
  vpc_security_group_ids = [aws_security_group.applications_rds_cluster.id]
  db_subnet_group_name   = "${aws_db_subnet_group.applications_rds_cluster.name}"
  engine                 = "aurora-postgresql"
  engine_mode            = "serverless"
  engine_version         = "9.6.11"
  master_username        = "user"
  master_password        = "password"
  # master_username         = data.aws_secretsmanager_secret_version.api_rds_username.secret_string
  # master_password         = data.aws_secretsmanager_secret_version.api_rds_password.secret_string
  deletion_protection     = local.account.aurora_serverless_deletion_protection
  backup_retention_period = 7
  skip_final_snapshot     = false

  scaling_configuration {
    auto_pause               = local.account.aurora_serverless_auto_pause
    max_capacity             = 2
    min_capacity             = 2
    seconds_until_auto_pause = 300
  }
  tags = local.default_tags
}

resource "aws_db_subnet_group" "applications_rds_cluster" {
  name       = "main"
  subnet_ids = data.aws_subnet_ids.private.ids

  tags = local.default_tags
}


resource "aws_security_group" "applications_rds_cluster_client" {
  name                   = "rds-cluster-client-${local.environment}"
  description            = "rds access for ${local.environment}"
  vpc_id                 = data.aws_vpc.default.id
  revoke_rules_on_delete = true
  tags                   = local.default_tags
}

resource "aws_security_group" "applications_rds_cluster" {
  name                   = "rds-cluster-applications-${local.environment}"
  description            = "api rds access"
  vpc_id                 = data.aws_vpc.default.id
  revoke_rules_on_delete = true
  tags                   = local.default_tags

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_security_group_rule" "applications_rds_cluster" {
  type                     = "ingress"
  from_port                = 5432
  to_port                  = 5432
  protocol                 = "tcp"
  source_security_group_id = aws_security_group.applications_rds_cluster_client.id
  security_group_id        = aws_security_group.applications_rds_cluster.id
}
