resource "local_file" "environment_pipeline_tasks_config" {
  content  = "${jsonencode(local.environment_pipeline_tasks_config)}"
  filename = "/tmp/environment_pipeline_tasks_config.json"
}

locals {
  public_front_url = "https://${aws_route53_record.public_front.name}"
  environment_pipeline_tasks_config = {
    account_id            = local.account.account_id
    cluster_name          = aws_ecs_cluster.lpa_refunds.name
    public_front_url     = local.public_front_url
    caseworker_front_url = "nothing here yet"
    tag                   = var.container_version
  }
}
