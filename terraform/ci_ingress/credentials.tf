terraform {
  backend "local" {}
}

provider "aws" {
  version = "2.70.0"
  region  = "eu-west-1"

  assume_role {
    role_arn     = "arn:aws:iam::${local.account.account_id}:role/${var.default_role}"
    session_name = "terraform-session"
  }
}

variable "default_role" {
  default = "opg-refunds-ci"
}

variable "management_role" {
  default = "opg-refunds-ci"
}

provider "aws" {
  version = "2.70.0"
  region  = "eu-west-1"
  alias   = "management"

  assume_role {
    role_arn     = "arn:aws:iam::311462405659:role/${var.management_role}"
    session_name = "terraform-session"
  }
}
