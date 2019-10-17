terraform {
  backend "local" {}
}

provider "aws" {
  region = "eu-west-1"
}

variable "default_role" {
  default = "opg-refunds-ci"
}

variable "management_role" {
  default = "opg-refunds-ci"
}

provider "aws" {
  region = "eu-west-1"
  alias  = "management"
}
