PHP ?= bin/php

.PHONY: docker
docker:
	docker build -f build.Dockerfile -t php-kafka .

.PHONY: check
check: phpcs phpunit phpstan

.PHONY: phpstan
phpstan:
	$(PHP) vendor/bin/phpstan analyse

.PHONY: phpunit
phpunit:
	$(PHP) vendor/bin/phpunit

.PHONY: phpcs
phpcs:
	$(PHP) vendor/bin/phpcs

.PHONY: phpcbf
phpcbf:
	$(PHP) vendor/bin/phpcbf
