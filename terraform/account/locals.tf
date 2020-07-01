# variables for terraform.tfvars.json
variable "account_mapping" {
  type = map
}

variable "accounts" {
  type = map(
    object({
      account_id                  = string
      is_production               = bool
      has_cloudfront_distribution = bool
      old_refunds_account_id      = string
      retention_in_days           = number
    })
  )
}

locals {
  opg_project = "lpa refunds"

  account_name = lookup(var.account_mapping, terraform.workspace, "development")
  account      = var.accounts[local.account_name]


  mandatory_moj_tags = {
    business-unit = "OPG"
    application   = "Claim a Lasting Power of Attorney Refund"
    owner         = "Amy Wilson: amy.wilson@digital.justice.gov.uk"
    is-production = local.account.is_production
  }

  optional_tags = {
    environment-name       = local.account_name
    infrastructure-support = "OPG LPA Product Team: opg-lpa-services@digital.justice.gov.uk"
    runbook                = "https://github.com/ministryofjustice/opg-refunds/tree/master/docs/runbooks"
    source-code            = "https://github.com/ministryofjustice/opg-refunds"
  }

  default_tags = merge(local.mandatory_moj_tags, local.optional_tags)
}
