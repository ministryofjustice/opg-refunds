resource "aws_db_parameter_group" "postgres-db-params" {
  name        = lower("postgres-db-params-${local.account_name}")
  description = "default postgres rds parameter group"
  family      = "postgres9.6"

  parameter {
    name         = "log_min_duration_statement"
    value        = "500"
    apply_method = "immediate"
  }

  parameter {
    name         = "log_statement"
    value        = "none"
    apply_method = "pending-reboot"
  }

  parameter {
    name         = "rds.log_retention_period"
    value        = "1440"
    apply_method = "immediate"
  }
}
