# === Config ===
DOCKER_COMPOSE := docker compose
PHP_CONTAINER := chronotrack-app

# === Artisan ===
artisan:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php artisan $(cmd)

migrate:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php artisan migrate

seed:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php artisan db:seed

fresh:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php artisan migrate:fresh --seed

tinker:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php artisan tinker

key-generate:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php artisan key:generate

cache-clear:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php artisan config:clear && \
	php artisan cache:clear && \
	php artisan route:clear && \
	php artisan view:clear

# === Composer ===
composer-install:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) composer install

composer-update:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) composer update

composer-require:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) composer require $(pkg)

# === NPM / Frontend ===
npm-install:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) npm install

npm-build:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) npm run build

npm-dev:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) npm run dev

# === Pest / Testing ===
test:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) ./vendor/bin/pest

# === Shell ===
sh:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) sh

bash:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) bash

# === Project Setup ===
install: composer-install npm-install key-generate migrate

rebuild: down build up install

up:
	$(DOCKER_COMPOSE) up -d

down:
	$(DOCKER_COMPOSE) down

restart:
	$(DOCKER_COMPOSE) down && $(DOCKER_COMPOSE) up -d

logs:
	$(DOCKER_COMPOSE) logs -f $(PHP_CONTAINER)

.PHONY: artisan migrate seed fresh tinker key-generate cache-clear \
        composer-install composer-update composer-require \
        npm-install npm-build npm-dev \
        test sh bash install rebuild up down restart logs
