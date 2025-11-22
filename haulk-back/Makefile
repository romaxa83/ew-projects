.SILENT:

include .env

#=============================
# Variables

site := ${APP_URL}
php_container := ${APP_NAME}_php

#======================================
# Information on commands, called by the "make" command

.DEFAULT_GOAL := help
.PHONY: help
help:  ## отображение данного сообщения help
	@awk 'BEGIN {FS = ":.*##"; printf "\n Usage:\n  make \033[36m<command>\033[0m\n \n Targets:\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-10s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST)

#======================================
# Сompound commands

.PHONY: up
up: docker_up info ## составная команда, для поднятия проекта [docker_up -> info]
.PHONY: restart
restart: down build up info ## составная команда, для перезапуска контейнера [down -> build -> up -> info]
.PHONY: init
init: down build docker_up app_init info ## составная команда, для первичного поднятия проекта, запускается один раз [down -> build -> docker_up -> app_init -> info]
.PHONY: test
test: test_init test_run ## составная команда, для запуска тестов [test_init -> test_run]
.PHONY: info
info: ps info_domen  ## составная команда, информация по проекту [ps -> info_domen]
#======================================
# Command

.PHONY: build
build: ## собирает контейнеры
	docker-compose build

.PHONY: docker_up
docker_up: ## подымает контейнеры
	docker-compose up -d

.PHONY: down
down: ## останавливает контейнеры и удаляет их образы
	docker-compose down --remove-orphans #очистит все запущенные контейнеры

.PHONY: ps
ps:	## информация по контейнерам докера
	docker-compose ps

.PHONY: api_generate
api_generate: ## генерация документации rest-api
	docker-compose exec php php artisan l5-swagger:generate

.PHONY: ide_helper
ide_helper: ## генерация файла, для корректного отображение фасадов в ide
	docker-compose exec php php artisan ide-helper:generate
	docker-compose exec php php artisan ide-helper:models
	docker-compose exec php php artisan ide-helper:meta

.PHONY: perm
perm: ## задает права папкам (vendor, storage)
	sudo chmod 777 -R -f vendor/
	sudo chmod 777 -R -f storage

.PHONY: app_init
app_init: ## Инициализация ларавеловского приложения (запускается один раз)
	docker-compose exec php composer install
	docker-compose exec php php artisan key:generate
	docker-compose exec php php artisan migrate
	docker-compose exec php php artisan db:seed
	docker-compose exec php php artisan passport:client --password --provider=users --name='Users'
	docker-compose exec php php artisan passport:client --password --provider=admins --name='Admins'

.PHONY: info_domen
info_domen: ## Линки на сервисы сайта (для разных окружений)
	echo '----------------------------------------------------------------------------------------------------------------------------------------';
	printf "\\033[36m[x] LOCAL\\033[0m\\n";
	echo ${site};
	echo ${site}'/telescope';
	echo ${site}':8025';
	echo ${site}'/api/documentation';
	echo ${site}'/laravel-websockets';
	echo '----------------------------------------------------------------------------------------------------------------------------------------';
	printf "\\033[36m[x] TEST\\033[0m\\n";
	echo 'Backoffice - https://backoffice-dev.haulk.app (admin@admin.com, 12345678)';
	echo 'CRM        - https://dev.haulk.app (wezomqa1@gmail.com, qwerty12)';
	echo 'BodyShop   - https://body-shop-dev.haulk.app (alekhina.i@wezom.com.ua, qwerty12)';
	echo 'Create Company   - https://auth-dev.haulk.app/carrier';
	echo 'SWAGGER    - https://dev.api.haulk.app/api/documentation';
	echo 'WEBSOCKET  - https://dev.api.haulk.app/laravel-websockets';
	echo 'TELESCOPE  - https://dev.api.haulk.app/telescope';
	echo '----------------------------------------------------------------------------------------------------------------------------------------';
	printf "\\033[36m[x] STAGE\\033[0m\\n";
	echo 'Backoffice - https://backoffice-stage.haulk.app (admin@admin.com, 12345678)';
	echo 'CRM        - https://stage.haulk.app (erofeeva.d.wezom@gmail.com, qwerty12)';
	echo 'BodyShop   - https://auth-stage.haulk.app/sign-in (alekhina.i@wezom.com.ua, qwerty12 Karakas123#)';
	echo 'SWAGGER    - https://stage.api.haulk.app/api/documentation';
	echo '----------------------------------------------------------------------------------------------------------------------------------------';
	printf "\\033[36m[x] PROD\\033[0m\\n";
	echo 'Backoffice - https://backoffice.haulk.app/ (admin@admin.com, 12345678)';
	echo 'CRM        - https://crm.haulk.app (twezom@gmail.com, qwerty13)';
	echo 'BodyShop   - https://auth.haulk.app/sign-in ( tyunikova.n@wezom.com,  Karakas123#)';
	echo 'SWAGGER    - https://crm.api.haulk.app/api/documentation';
	echo '----------------------------------------------------------------------------------------------------------------------------------------';
#======================================
# Into container

.PHONY: php_bash
php_bash: ## зайти в контейнер php
	docker exec -it $(php_container) bash

#======================================
# Test Command

.PHONY: test_init
test_init: ## настройка тестового окружения
	docker-compose run --rm php php artisan key:generate -n --env=testing
	docker-compose run --rm php php artisan migrate -n --env=testing

.PHONY: test_run
test_run: ## запускает тесты
	docker-compose exec php php ./vendor/bin/phpunit

.PHONY: test_coverage_html
test_coverage_html: ## генерация отчета по тестам (html)
	docker-compose exec --env XDEBUG_MODE=coverage php php  ./vendor/bin/phpunit --coverage-html ./public/test/v-07/report

.PHONY: test_coverage_text
test_coverage_text: ## генерация отчета по тестам (text)
	docker-compose exec --env XDEBUG_MODE=coverage php php  ./vendor/bin/phpunit --coverage-text

UID := $(shell id -u)
GID := $(shell id -g)

.PHONY: user
user: ## user
	echo 'Current User UID: $(UID)';
	echo 'Current User GID: $(GID)';

.PHONY: list
list: ## list
	@ls -la;
