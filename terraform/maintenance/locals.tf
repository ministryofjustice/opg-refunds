locals {
  opg_project = "lpa refunds"

  mandatory_moj_tags = {
    business-unit = "OPG"
    application   = "Claim a Lasting Power of Attorney Refund"
    owner         = "Amy Wilson: amy.wilson@digital.justice.gov.uk"
    is-production = "No"
  }

  optional_tags = {
    environment-name       = "maintenance"
    infrastructure-support = "OPG LPA Product Team: opgteam+lpa-refunds@digital.justice.gov.uk"
    runbook                = "https://github.com/ministryofjustice/opg-webops-runbooks/tree/master/LPA"
    source-code            = "https://github.com/ministryofjustice/opg-lpa"
  }

  default_tags = merge(local.mandatory_moj_tags, local.optional_tags)
}
