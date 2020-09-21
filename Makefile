SHELL := '/bin/bash'
NOTIFY := $(shell aws-vault exec moj-refunds-dev -- aws secretsmanager get-secret-value --secret-id development/opg_refunds_notify_api_key | jq -r .'SecretString')

.PHONY: all
all:
	@${MAKE} dc-up

.PHONY: dc-run
dc-run:
	@export OPG_REFUNDS_NOTIFY_API_KEY=${NOTIFY}; \
	docker-compose run public-composer

	@export OPG_REFUNDS_NOTIFY_API_KEY=${NOTIFY}; \
	docker-compose run caseworker-front-composer

	@export OPG_REFUNDS_NOTIFY_API_KEY=${NOTIFY}; \
	docker-compose run caseworker-api-composer


.PHONY: dc-up
dc-up:
	@export OPG_REFUNDS_NOTIFY_API_KEY=${NOTIFY}; \
	docker-compose up

.PHONY: dc-build
dc-build:
	@export OPG_REFUNDS_NOTIFY_API_KEY=${NOTIFY}; \
	docker-compose build


.PHONY: dc-build-clean
dc-build-clean:
	@export OPG_REFUNDS_NOTIFY_API_KEY=${NOTIFY}; \
	docker-compose build --no-cache

.PHONY: dc-down
dc-down:
	@export OPG_REFUNDS_NOTIFY_API_KEY=${NOTIFY}; \
	docker-compose down
