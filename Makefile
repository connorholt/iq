.PHONY: dev-up dev-build dev-start dev-clear sf composer dev-php-analyze dev-restart phpunit dev-supervisord-run

PROJECT_NAME ?= member-bot

REL_PROJECT := $(PROJECT_NAME)$(BUILD_ID)

DEV_COMPOSE_FILE := ./docker-compose.yml

dev-start:
	${INFO} "Go go go build.."
	@ docker-compose build --no-cache
	${INFO} "Starting up"
	@ docker-compose up -d
	${INFO} "Chmod on var/log"
	@ docker-compose run --rm -u root iq-php chmod -R 777 var/logs
	${INFO} "Run composer update"
	@ docker-compose run --rm iq-php composer update
	${INFO} "Clear cache symfony"
	@ docker-compose run --rm iq-php php bin/console cache:clear
	${INFO} "Run migrations"
	@ docker-compose run --rm iq-php php bin/console doctrine:migrations:migrate
	${INFO} "Now we are down all container"
	@ docker-compose down
	${INFO} "Completed, run dev-up"

dev-build:
	${INFO} "Starting docker-compose build.."
	@ docker-compose build --no-cache
	${INFO} "Completed, run dev-up"

dev-up:
	${INFO} "Copy hooks to .git"
	cp git_hooks/pre-commit ./.git/hooks/
	${INFO} "Starting docker-compose up"
	@ docker-compose up -d
	${INFO} "Run composer install"
	@ docker-compose run --rm iq-php composer update
	${INFO} "Clear cache symfony"
	@ docker-compose run --rm iq-php php bin/console cache:clear
	${INFO} "Run migrations"
	@ docker-compose run --rm iq-php php bin/console doctrine:migrations:migrate
	${DANGER} "Read psalm"
	docker-compose run --rm iq-php vendor/bin/psalm
	${INFO} "Run supervisord"
	@ docker-compose run iq-php supervisord -n &
	${INFO} "Completed, check http://localhost:8080/"

dev-supervisord-run:
	${INFO} "Run supervisord"
	@ docker-compose run php service supervisor restart

dev-php-analyze:
	${INFO} "Read psalm"
	docker-compose run --rm php vendor/bin/psalm

dev-down:
	${INFO} "Starting docker-compose down"
	@ docker-compose down

phpunit:
	${DANGER} "Phpunit"
	@ docker-compose run php ./vendor/bin/phpunit

dev-restart:
	${INFO} "Kill docker"
	@ make dev-down
	${INFO} "Up docker"
	@ make dev-up

dev-clear:
	${INFO} "Remove all containers and images"
	@ docker rm -f `docker ps -q -a`
	@ docker rmi -f `docker images -q`

composer:
	${INFO} "composer $(COMPOSER_ARGS)"
	@ docker-compose run --rm php composer $(COMPOSER_ARGS)

sf:
	${INFO} "sf $(SF_ARGS)"
	@ docker-compose run --rm php php bin/console $(SF_ARGS)

# Cosmetics
YELLOW := "\033[32m"
RED := "\033[31m"
NC := "\e[0m"

# Shell Functions
INFO := @bash -c '\
  printf $(YELLOW); \
  echo "===> $$1"; \
  printf $(NC)' VALUE

DANGER := @bash -c '\
  printf $(RED); \
  echo "===> $$1"; \
  printf $(NC)' VALUE

# Extract sf arguments
ifeq (sf,$(firstword $(MAKECMDGOALS)))
  SF_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
  ifneq ($(SF_ARGS),)
    $(eval $(SF_ARGS):;@:)
  endif
endif

# Extract composer arguments
ifeq (composer,$(firstword $(MAKECMDGOALS)))
  COMPOSER_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
  ifneq ($(COMPOSER_ARGS),)
    $(eval $(COMPOSER_ARGS):;@:)
  endif
endif