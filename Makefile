REFUND_REPOS = opg-refunds-maintenance
REFUND_HELPER_REPOS = opg-refunds-caseworker-datamodels
REFUND_PRIVATE_REPOS = lpa-refund-local-dev
containers = public-front caseworker-front caseworker-api

ifdef stage
	stagearg = --stage $(stage)
endif

currenttag := $(shell semvertag latest $(stagearg))
newtag := $(shell semvertag bump patch $(stagearg))

registryUrl ?= registry.service.opg.digital

.PHONY: local build push pull $(containers) clean showinfo

local:
	@for REPO in $(REFUND_PRIVATE_REPOS); do \
		if [ ! -d "../$$REPO" ]; then \
			git clone --branch master git@gitlab.service.opg.digital:lpa-refunds/$$REPO.git ../$$REPO; \
		fi \
	done

	@for REPO in $(REFUND_REPOS); do \
		if [ ! -d "../$$REPO" ]; then \
			git clone --branch master git@github.com:ministryofjustice/$$REPO.git ../$$REPO; \
		fi \
	done

	@for REPO in $(REFUND_HELPER_REPOS); do \
		if [ ! -d "../$$REPO" ]; then \
			git clone --branch master git@github.com:ministryofjustice/$$REPO.git ../$$REPO; \
		fi \
	done

build: $(containers)

$(containers):
	$(MAKE) -C $@ newtag=${newtag}

push:
	for i in $(containers); do \
		docker push ${registryUrl}/$$i; \
		docker push ${registryUrl}/$$i:${newtag}; \
	done

	semvertag tag $(newtag)
	cd ./public-front && semvertag tag $(newtag)
	cd ./caseworker-front && semvertag tag $(newtag)
	cd ./caseworker-api && semvertag tag $(newtag)
	cd ../opg-refunds-maintenance && semvertag tag $(newtag)
	@echo "RELEASE_TAG=$(newtag)" > ../trigger.properties

pull:
	for i in $(containers); do \
		docker pull ${registryUrl}/$$i:${currenttag}; \
	done

clean:
	for i in $(containers); do \
		docker rmi $(registryUrl)/$$i:$(newtag) || true ; \
		docker rmi $(registryUrl)/$$i || true ; \
	done

showinfo:
	@echo Registry: $(registryUrl)
	@echo Newtag: $(newtag)
	@echo Current Tag: $(currenttag)
	@echo Container List: $(containers)
	@echo Tagging repo: $(tagrepo)

all: showinfo build push clean
