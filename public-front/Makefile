.PHONY: build

registryUrl = registry.service.opg.digital
image = opg-refunds-public-front
docker_dir = ./opg-refunds-public-front

build:
	cd ${docker_dir} && docker build -t "$(registryUrl)/$(image)" .
	cd ${docker_dir} && docker tag "$(registryUrl)/$(image)" "$(registryUrl)/$(image):$(newtag)"