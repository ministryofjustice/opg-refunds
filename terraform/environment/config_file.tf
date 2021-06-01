resource "local_file" "environment_pipeline_tasks_config" {
  content  = jsonencode(local.environment_pipeline_tasks_config)
  filename = "/tmp/environment_pipeline_tasks_config.json"
}

locals {
  environment_pipeline_tasks_config = {
    account_id  = local.account.account_id
    environment = local.environment
    tag         = var.container_version
  }
}
