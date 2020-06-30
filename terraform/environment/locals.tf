# variables for terraform.tfvars.json
variable "account_mapping" {
  type = map
}

variable "container_version" {
  type    = string
  default = "latest"
}

variable "accounts" {
  type = map(
    object({
      account_id                                   = string
      is_production                                = bool
      aurora_serverless_auto_pause                 = bool
      database_deletion_protection                 = bool
      skip_final_snapshot                          = bool
      backup_retention_period                      = number
      has_cloudfront_distribution                  = bool
      prefix_enabled                               = bool
      public_front_autoscaling_maximum             = number
      public_front_autoscaling_metric_track_cpu    = number
      public_front_autoscaling_metric_track_memory = number
      opg_refunds_stack_type                       = string
      days_to_wait_before_expiry                   = number
      opg_refunds_google_analytics_tracking_id     = string
      opg_refunds_google_analytics_tracking_gov_id = string

    })
  )
}

locals {
  opg_project = "lpa refunds"

  account_name = lookup(var.account_mapping, terraform.workspace, "development")
  account      = var.accounts[local.account_name]
  environment  = lower(terraform.workspace)
  dns_prefix   = local.account.prefix_enabled ? "${local.environment}." : ""

  rds_master_username = "root"

  mandatory_moj_tags = {
    business-unit = "OPG"
    application   = "Claim a Lasting Power of Attorney Refund"
    owner         = "Amy Wilson: amy.wilson@digital.justice.gov.uk"
    is-production = local.account.is_production
  }

  optional_tags = {
    environment-name       = local.environment
    infrastructure-support = "OPG LPA Product Team: opg-lpa-services@digital.justice.gov.uk"
    runbook                = "https://github.com/ministryofjustice/opg-refunds/tree/master/docs/runbooks"
    source-code            = "https://github.com/ministryofjustice/opg-refunds"
  }

  default_tags = merge(local.mandatory_moj_tags, local.optional_tags)
}
