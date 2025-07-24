COMPOSER ?= composer
PHPUNIT = ./vendor/bin/phpunit
PHPSTAN = ./vendor/bin/phpstan
PHPCSFIXER = ./vendor/bin/php-cs-fixer
PHPUNIT_FLAGS = --configuration ./phpunit.xml.dist

# Commands
.PHONY: phpstan phpcs-analyze phpcs-fix help php-test

php-test:
	$(PHPUNIT) $(PHPUNIT_FLAGS)

phpstan:
	@$(PHPSTAN) analyse

phpcs-report:
	@$(PHPCSFIXER) check || true

phpcs-fix:
	@$(PHPCSFIXER) fix

php-lint: phpcs-report phpstan

composer-dev:
	@$(COMPOSER) install --no-interaction --no-progress --prefer-dist
