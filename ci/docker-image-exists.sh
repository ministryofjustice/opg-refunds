#!/bin/bash
set -e

if [ "$#" -ne 1 ]; then
    echo "Invalid args passed. Usage: $0 <image:tag>"
    exit 1
fi

# arrIN=$1
# arrIN=(${1//:/ })

IMAGE=$(echo "$1" | awk -F ":" '{print $1}')
TAG=$(echo "$1" | awk -F ":" '{print $2}')

# Check if it exists - if not then exit 1
if [[ -z $(docker images -q "${IMAGE}:${TAG}") ]]; then
  echo "IMAGE NOT FOUND: ${IMAGE}:${TAG}"
  exit 1
fi

echo "IMAGE FOUND: ${IMAGE}:${TAG}"
exit 0
