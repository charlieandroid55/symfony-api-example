# Project config ———————————————————————————————————
PROJECT_PORT	= 7990

# Setup ———————————————————————————————————
SHELL         = bash
EXEC_PHP      = php8.2
GIT           = git
SYMFONY       = $(EXEC_PHP) bin/console
SYMFONY_BIN   = symfony
COMPOSER      = composer
BREW          = brew
.DEFAULT_GOAL = help



## —— 🐝 Symfony Makefile 🐝 ———————————————————————————————————
help: ## Despliega la ayuda
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Project 🐝 ———————————————————————————————————————————————————————————————
gesdinet-jwt-clear: ## Revoke all invalid tokens
	$(SYMFONY_BIN) console gesdinet:jwt:clear

restore-database: ## Construye la BD, controla la validad del schema, carga los fixtures y chequea el estado de la migración
	rm -rf var/cache/* var/logs/*
	$(SYMFONY_BIN) console doctrine:database:drop --if-exists --force
	$(SYMFONY_BIN) console doctrine:databas:create
	$(SYMFONY_BIN) console doctrine:schema:create
	$(SYMFONY_BIN) console doctrine:schema:validate
	$(SYMFONY_BIN) console app:create:user

## —— Composer ————————————————————————————————————————————————————————————
composer-install: composer.json ## Instala los vendors acorde al fichero composer.json y el composer.lock
	$(COMPOSER) install -vv
composer-update: composer.json ## Actualiza los vendors acorde al fichero composer.json
	$(COMPOSER) update -vv

## —— Symfony binary ————————————————————————————————————————————————————————
bin-install: ## Descarga e instala el binario en el proyecto
	curl -sS https://get.symfony.com/cli/installer | bash
	mv ~/.symfony/bin/symfony .

cert-install: symfony ## Instala el certificados local HTTPS
	$(SYMFONY_BIN) server:ca:install

serve: ## Servidor de la aplicación con soporte HTTPS
	$(SYMFONY_BIN) serve --daemon --port=$(PROJECT_PORT)

status-serve: ## Estado del server HTTPS
	$(SYMFONY_BIN) serve:status

unserve: ## Para el webserver
	$(SYMFONY_BIN) server:stop