resource "local_file" "environment_pipeline_tasks_config" {
  content  = jsonencode(local.environment_pipeline_tasks_config)
  filename = "/tmp/environment_pipeline_tasks_config.json"
}

locals {
  environment_pipeline_tasks_config = {
    account_id                             = local.account.account_id
    cluster_name                           = aws_ecs_cluster.lpa_refunds.name
    public_front_fqdn                      = aws_route53_record.public_front.fqdn
    caseworker_front_fqdn                  = aws_route53_record.caseworker_front.fqdn
    claim_service_gov_uk_fqdn              = aws_route53_record.claim-power-of-attorney-refund_service_gov_uk.fqdn
    caseworker_refunds_opg_digital_fqdn    = aws_route53_record.caseworker_refunds_opg_digital.fqdn
    environment                            = local.environment
    tag                                    = var.container_version
    caseworker_db_client_security_group_id = aws_security_group.caseworker_rds_cluster_client.id
    seeding_security_group_id              = aws_security_group.seeding_ecs_service.id
  }
}
