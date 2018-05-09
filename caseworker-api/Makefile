.PHONY: build

registryUrl = registry.service.opg.digital
image = opg-refunds-caseworker-api
docker_dir = ./caseworker-api

build:
	cd ${docker_dir} && docker build -t "$(registryUrl)/$(image)" .
	cd ${docker_dir} && docker tag "$(registryUrl)/$(image)" "$(registryUrl)/$(image):$(newtag)"