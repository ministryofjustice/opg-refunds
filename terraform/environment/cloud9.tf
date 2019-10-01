resource "aws_security_group_rule" "caseworker_rds_cloud9_in" {
  type              = "ingress"
  protocol          = "tcp"
  from_port         = 5432
  to_port           = 5432
  security_group_id = aws_security_group.caseworker_rds_cluster_client.id
  cidr_blocks       = [data.aws_vpc.default.cidr_block]
}
resource "aws_security_group_rule" "applications_rds_cloud9_in" {
  type              = "ingress"
  protocol          = "tcp"
  from_port         = 5432
  to_port           = 5432
  security_group_id = aws_security_group.applications_rds_cluster_client.id
  cidr_blocks       = [data.aws_vpc.default.cidr_block]
}
