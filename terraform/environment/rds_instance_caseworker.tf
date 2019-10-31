resource "aws_db_instance" "caseworker" {
  identifier                 = lower("caseworker-${local.environment}")
  name                       = "caseworker"
  allocated_storage          = 20
  max_allocated_storage      = 100
  storage_type               = "gp2"
  storage_encrypted          = true
  engine                     = "postgres"
  engine_version             = "9.6.11"
  instance_class             = "db.m3.medium"
  port                       = "5432"
  username                   = local.rds_master_username
  password                   = data.aws_secretsmanager_secret_version.postgres_password.secret_string
  parameter_group_name       = aws_db_parameter_group.postgres-db-params.name
  option_group_name          = "default:postgres-9-6"
  vpc_security_group_ids     = [aws_security_group.caseworker_rds_instance.id, aws_security_group.caseworker_rds_instance_client.id]
  db_subnet_group_name       = aws_db_subnet_group.caseworker_rds_instance.name
  multi_az                   = true
  auto_minor_version_upgrade = true
  maintenance_window         = "sun:01:00-sun:01:30"
  backup_retention_period    = 14
  skip_final_snapshot        = true
  deletion_protection        = local.account.database_deletion_protection
  tags                       = local.default_tags
}

resource "aws_db_subnet_group" "caseworker_rds_instance" {
  name       = "caseworker-db-instance-${local.environment}"
  subnet_ids = data.aws_subnet_ids.private.ids

  tags = local.default_tags
}

resource "aws_security_group" "caseworker_rds_instance_client" {
  name                   = "${local.environment}-caseworker-rds-instance-client"
  description            = "client access to caseworker db instance"
  vpc_id                 = data.aws_vpc.default.id
  revoke_rules_on_delete = true
  tags                   = local.default_tags
}

resource "aws_security_group" "caseworker_rds_instance" {
  name                   = "${local.environment}-caseworker-rds-instance"
  description            = "caseworker db instance access"
  vpc_id                 = data.aws_vpc.default.id
  revoke_rules_on_delete = true
  tags                   = local.default_tags

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_security_group_rule" "caseworker_rds_instance" {
  type                     = "ingress"
  from_port                = 5432
  to_port                  = 5432
  protocol                 = "tcp"
  source_security_group_id = aws_security_group.caseworker_rds_instance_client.id
  security_group_id        = aws_security_group.caseworker_rds_instance.id
}
