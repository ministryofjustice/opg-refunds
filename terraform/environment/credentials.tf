terraform {
  backend "s3" {
    bucket         = "opg.terraform.state"
    key            = "moj-lpa-refunds-environment/terraform.tfstate"
    encrypt        = true
    region         = "eu-west-1"
    role_arn       = "arn:aws:iam::311462405659:role/opg-refunds-ci"
    dynamodb_table = "remote_lock"
  }
}

variable "default_role" {
  default = "opg-refunds-ci"
}

variable "old_account_default_role" {
  default = "opg-refunds-ci"
}

provider "aws" {
  version = "2.70.0"
  region  = "eu-west-1"

  assume_role {
    role_arn     = "arn:aws:iam::${local.account.account_id}:role/${var.default_role}"
    session_name = "terraform-session"
  }
}

provider "aws" {
  version = "2.70.0"
  alias   = "us_east_1"
  region  = "us-east-1"

  assume_role {
    role_arn     = "arn:aws:iam::${local.account.account_id}:role/${var.default_role}"
    session_name = "terraform-session"
  }
}


provider "aws" {
  version = "2.70.0"
  region  = "eu-west-1"
  alias   = "management"

  assume_role {
    role_arn     = "arn:aws:iam::311462405659:role/${var.old_account_default_role}"
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
