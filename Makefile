REFUND_REPOS = opg-refunds-maintenance
REFUND_HELPER_REPOS = opg-refunds-caseworker-datamodels
REFUND_PRIVATE_REPOS = lpa-refund-local-dev

.PHONY: local

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