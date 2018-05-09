.PHONY: build

registryUrl = registry.service.opg.digital
image = opg-refunds-local-sns
docker_dir = .

build:
	cd ${docker_dir} && docker build -t "$(registryUrl)/$(image)" .
	cd ${docker_dir} && docker tag "$(registryUrl)/$(image)" "$(registryUrl)/$(image):$(newtag)"
