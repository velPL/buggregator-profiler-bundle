# Variables definitions
PHPUNIT = ./vendor/bin/phpunit
PHPUNIT_FLAGS = --configuration ./phpunit.xml.dist

# Commands
.PHONY: php-test
php-test:
	$(PHPUNIT) $(PHPUNIT_FLAGS)
