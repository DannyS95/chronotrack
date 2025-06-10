# === Config ===
DOCKER_COMPOSE := docker compose
PHP_CONTAINER := chronotrack-app

# === Artisan ===
artisan:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php artisan $(cmd)

migrate:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php artisan migrate $(ARGS)

seed:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php artisan db:seed $(ARGS)

fresh:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php artisan migrate:fresh $(ARGS)

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
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) composer install $(ARGS)

composer-update:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) composer update $(ARGS)

composer-require:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) composer require $(pkg)

# === NPM / Frontend ===
npm-install:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) npm install $(ARGS)

npm-build:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) npm run build $(ARGS)

npm-dev:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) npm run dev $(ARGS)

# === Pest / Testing ===
test:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) ./vendor/bin/pest $(ARGS)

# === Shell ===
sh:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) sh

bash:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) bash

up:
	$(DOCKER_COMPOSE) up -d

down:
	$(DOCKER_COMPOSE) down

restart:
	$(DOCKER_COMPOSE) down && $(DOCKER_COMPOSE) up -d

logs:
	$(DOCKER_COMPOSE) logs -f $(PHP_CONTAINER)

install: ensure-env
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) composer install
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) npm install
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php artisan key:generate
	sleep 10
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php artisan migrate
	make dev

ensure-env:
	@if [ ! -f .env ]; then \
		echo "Creating .env from .env.example..."; \
		cp .env.example .env; \
	fi

reset:
	docker compose down -v --remove-orphans
	docker compose up -d --build
	sleep 10
	make install

dev:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php artisan serve --host=0.0.0.0 --port=8000

.PHONY: artisan migrate seed fresh tinker key-generate cache-clear \
        composer-install composer-update composer-require \
        npm-install npm-build npm-dev \
        test sh bash install rebuild up down restart logs
