terraform {
  backend "s3" {
    bucket         = "opg.terraform.state"
    key            = "moj-lpa-refunds-migration/terraform.tfstate"
    encrypt        = true
    region         = "eu-west-1"
    role_arn       = "arn:aws:iam::311462405659:role/opg-refunds-ci"
    dynamodb_table = "remote_lock"
  }
}

variable "old_account_default_role" {
  default = "ci"
}

provider "aws" {
  version = "2.70.0"
  region  = "eu-west-1"
  # old-refunds-development

  assume_role {
    role_arn     = "arn:aws:iam::792093328875:role/${var.old_account_default_role}"
    session_name = "terraform-session"
  }
}

provider "aws" {
  version = "2.70.0"
  alias   = "old_refunds_production"
  region  = "eu-west-1"
  # old-refunds-production

  assume_role {
    role_arn     = "arn:aws:iam::574983609246:role/${var.old_account_default_role}"
    session_name = "terraform-session"
  }
}
