data "aws_vpc" "default" {
  default = true
}

data "aws_security_group" "caseworker_rds_cluster_client" {
  name = "${local.environment}-caseworker-rds-cluster-client"
}

data "aws_security_group" "applications_rds_cluster_client" {
  name = "${local.environment}-applications-rds-cluster-client"
}

resource "aws_security_group_rule" "caseworker_rds_cloud9_in" {
  count             = local.account.allow_ingress_modification ? 1 : 0
  type              = "ingress"
  protocol          = "tcp"
  from_port         = 5432
  to_port           = 5432
  security_group_id = aws_security_group.caseworker_rds_cluster_client.id
  cidr_blocks       = [data.aws_vpc.default.cidr_block]
}
resource "aws_security_group_rule" "applications_rds_cloud9_in" {
  count             = local.account.allow_ingress_modification ? 1 : 0
  type              = "ingress"
  protocol          = "tcp"
  from_port         = 5432
  to_port           = 5432
  security_group_id = aws_security_group.applications_rds_cluster_client.id
  cidr_blocks       = [data.aws_vpc.default.cidr_block]
}
