resource "local_file" "environment_pipeline_tasks_config" {
  content  = "${jsonencode(local.environment_pipeline_tasks_config)}"
  filename = "/tmp/environment_pipeline_tasks_config.json"
}

locals {
  environment_pipeline_tasks_config = {
    account_id            = local.account.account_id
    cluster_name          = aws_ecs_cluster.lpa_refunds.name
    public_front_fqdn     = aws_route53_record.public_front.name
    caseworker_front_fqdn = "nothing here yet"
    tag                   = var.container_version
  }
}
