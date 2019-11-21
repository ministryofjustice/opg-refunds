resource "aws_rds_cluster" "applications" {
  cluster_identifier     = "applications-${local.environment}"
  vpc_security_group_ids = [aws_security_group.applications_rds_cluster.id]
  db_subnet_group_name   = aws_db_subnet_group.applications_rds_cluster.name

  engine                          = "aurora-postgresql"
  engine_version                  = "9.6.11"
  db_cluster_parameter_group_name = "default.aurora-postgresql9.6"
  database_name                   = "applications"

  backtrack_window  = 0
  apply_immediately = true
  storage_encrypted = true

  master_username = local.rds_master_username
  master_password = data.aws_secretsmanager_secret_version.postgres_password.secret_string

  deletion_protection             = local.account.database_deletion_protection
  enabled_cloudwatch_logs_exports = []
  backup_retention_period         = 7
  preferred_backup_window         = "00:14-00:44"
  preferred_maintenance_window    = "wed:22:26-wed:22:56"
  skip_final_snapshot             = true
  tags                            = local.default_tags
  lifecycle = {
    ignore_changes = [engine_version]
  }
}

resource "aws_rds_cluster_instance" "applications_cluster_instances" {
  count              = 1
  identifier         = "applications-${local.environment}-${count.index}"
  cluster_identifier = aws_rds_cluster.applications.id
  instance_class     = "db.r4.large"
  engine             = aws_rds_cluster.applications.engine
  engine_version     = aws_rds_cluster.applications.engine_version
}

resource "aws_db_subnet_group" "applications_rds_cluster" {
  name       = "applications-db-cluster-${local.environment}"
  subnet_ids = data.aws_subnet_ids.private.ids

  tags = local.default_tags
}


resource "aws_security_group" "applications_rds_cluster_client" {
  name                   = "${local.environment}-applications-rds-cluster-client"
  description            = "client access to applications db cluster"
  vpc_id                 = data.aws_vpc.default.id
  revoke_rules_on_delete = true
  tags                   = local.default_tags
}

resource "aws_security_group" "applications_rds_cluster" {
  name                   = "${local.environment}-applications-rds-cluster"
  description            = "applications db cluster access"
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
