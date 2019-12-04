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
      account_id                               = string
      is_production                            = bool
      public_front_certificate_domain_name     = string
      public_front_dns                         = string
      caseworker_front_certificate_domain_name = string
      caseworker_front_dns                     = string
      aurora_serverless_auto_pause             = bool
      database_deletion_protection             = bool
      has_cloudfront_distribution              = bool
      put_claim_fqdn_into_maintenance          = bool
    })
  )
}

locals {
  opg_project = "lpa refunds"

  account_name      = lookup(var.account_mapping, terraform.workspace, "development")
  account           = var.accounts[local.account_name]
  environment       = lower(terraform.workspace)
  dns_namespace_env = local.account_name == "production" ? "" : "${local.environment}."

  rds_master_username = "root"

  mandatory_moj_tags = {
    business-unit = "OPG"
    application   = "Claim a Lasting Power of Attorney Refund"
    owner         = "Amy Wilson: amy.wilson@digital.justice.gov.uk"
    is-production = local.account.is_production
  }

  optional_tags = {
    environment-name       = local.environment
    infrastructure-support = "OPG LPA Product Team: opgteam+lpa-refunds@digital.justice.gov.uk"
    runbook                = "https://github.com/ministryofjustice/opg-webops-runbooks/tree/master/LPA"
    source-code            = "https://github.com/ministryofjustice/opg-lpa"
  }

  default_tags = merge(local.mandatory_moj_tags, local.optional_tags)
}
