.DEFAULT_GOAL := test

COMPOSER ?= composer

.PHONY: install
install:
	$(COMPOSER) install

.PHONY: update
update:
	$(COMPOSER) update

.PHONY: phpstan
phpstan:
	vendor/bin/phpstan analyse

.PHONY: pest
pest:
	vendor/bin/pest

.PHONY: coverage
coverage:
	vendor/bin/pest --coverage

.PHONY: integration
integration:
	vendor/bin/pest --testsuite=integration

.PHONY: docker-integration
docker-integration:
	docker compose run --rm test sh -lc 'composer install --no-interaction --no-progress && vendor/bin/pest --testsuite=integration'

.PHONY: docker-clean
docker-clean:
	docker compose down --volumes --remove-orphans

.PHONY: test
test: phpstan pest

.PHONY: ci
ci: phpstan coverage

.PHONY: normalize
normalize:
	$(COMPOSER) normalize

.PHONY: clean
clean:
	rm -rf .phpunit.cache vendor coverage clover.xml
