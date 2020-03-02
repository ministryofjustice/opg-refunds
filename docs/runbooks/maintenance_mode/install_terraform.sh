#!/usr/bin/env bash

function install_terraform() {
  curl https://releases.hashicorp.com/terraform/${TERRAFORM_VERSION}/terraform_${TERRAFORM_VERSION}_linux_amd64.zip > terraform_${TERRAFORM_VERSION}_linux_amd64.zip
  echo "${TERRAFORM_SHA256SUM}  terraform_${TERRAFORM_VERSION}_linux_amd64.zip" > terraform_${TERRAFORM_VERSION}_SHA256SUMS
  sha256sum -c --status terraform_${TERRAFORM_VERSION}_SHA256SUMS
  sudo unzip terraform_${TERRAFORM_VERSION}_linux_amd64.zip -d /bin
  rm -f terraform_${TERRAFORM_VERSION}_linux_amd64.zip
}

function parse_args() {
  for arg in "$@"
  do
      case $arg in
          -v|--version)
          TERRAFORM_VERSION="$2"
          shift
          shift
          ;;
          -s|--sha256sum)
          TERRAFORM_SHA256SUM="$2"
          shift
          ;;
      esac
  done
}

parse_args $@
install_terraform
