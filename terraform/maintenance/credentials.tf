terraform {
  backend "s3" {
    bucket         = "opg.terraform.state"
    key            = "moj-lpa-refunds-maintenance/terraform.tfstate"
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
  default = "ci"
}

provider "aws" {
  region = "eu-west-1"
  # moj-refunds-development

  assume_role {
    role_arn     = "arn:aws:iam::936779158973:role/${var.default_role}"
    session_name = "terraform-session"
  }
}

provider "aws" {
  alias  = "eu_central_1"
  region = "eu-central-1"
  # moj-refunds-development

  assume_role {
    role_arn     = "arn:aws:iam::936779158973:role/${var.default_role}"
    session_name = "terraform-session"
  }
}

provider "aws" {
  alias  = "us_east_1"
  region = "us-east-1"
  # moj-refunds-development

  assume_role {
    role_arn     = "arn:aws:iam::936779158973:role/${var.default_role}"
    session_name = "terraform-session"
  }
}

provider "aws" {
  alias  = "old_refunds_production"
  region = "eu-west-1"
  # old-refunds-production

  assume_role {
    role_arn     = "arn:aws:iam::574983609246:role/${var.old_account_default_role}"
    session_name = "terraform-session"
  }
}

provider "aws" {
  alias  = "new_refunds_production"
  region = "eu-west-1"
  # new-refunds-production

  assume_role {
    role_arn     = "arn:aws:iam::805626386523:role/${var.default_role}"
    session_name = "terraform-session"
  }
}

provider "aws" {
  alias  = "management"
  region = "eu-west-1"
  # new-refunds-production

  assume_role {
    role_arn     = "arn:aws:iam::311462405659:role/${var.default_role}"
    session_name = "terraform-session"
  }
}