variable "cloud9_ip" {
  description = "IP address for CircleCI docker remote"
  default     = ""
}

locals {
  local_ip_cidr = "${chomp(var.cloud9_ip)}/32"
}

data "aws_security_group" "caseworker_rds_cluster_client" {
  name = "${local.environment}-caseworker-rds-cluster-client"
}

data "aws_security_group" "applications_rds_cluster_client" {
  name = "${local.environment}-applications-rds-cluster-client"
}

resource "aws_security_group_rule" "caseworker_rds_cloud9_in" {
  count             = local.account.allow_ingress_modification ? 1 : 0
  description       = "cloud9 ingress for databases"
  type              = "ingress"
  protocol          = "tcp"
  from_port         = 5432
  to_port           = 5432
  security_group_id = data.aws_security_group.caseworker_rds_cluster_client.id
  cidr_blocks       = [local.local_ip_cidr]
}
resource "aws_security_group_rule" "applications_rds_cloud9_in" {
  count             = local.account.allow_ingress_modification ? 1 : 0
  description       = "cloud9 ingress for databases"
  type              = "ingress"
  protocol          = "tcp"
  from_port         = 5432
  to_port           = 5432
  security_group_id = data.aws_security_group.applications_rds_cluster_client.id
  cidr_blocks       = [local.local_ip_cidr]
}
