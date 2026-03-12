.PHONY: help up down build install shell db-create migrate test jwt-keys

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

up: ## Start all containers
	docker compose up -d

down: ## Stop all containers
	docker compose down

build: ## Build Docker images
	docker compose build --no-cache

install: ## Install composer dependencies
	docker compose run --rm php composer install

shell: ## Open shell in PHP container
	docker compose exec php sh

db-create: ## Create database
	docker compose exec php php bin/console doctrine:database:create --if-not-exists

migrate: ## Run database migrations
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

migration-diff: ## Generate new migration
	docker compose exec php php bin/console doctrine:migrations:diff

test: ## Run PHPUnit tests
	docker compose exec php php vendor/bin/phpunit

jwt-keys: ## Generate JWT keys
	docker compose exec php mkdir -p config/jwt
	docker compose exec php openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:$${JWT_PASSPHRASE:-task_manager_jwt_passphrase}
	docker compose exec php openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:$${JWT_PASSPHRASE:-task_manager_jwt_passphrase}

setup: build up install db-create migrate jwt-keys ## Full project setup from scratch
	@echo "\n✅ Setup complete! App running at http://localhost:8080"

logs: ## Show PHP container logs
	docker compose logs -f php

cache-clear: ## Clear Symfony cache
	docker compose exec php php bin/console cache:clear
