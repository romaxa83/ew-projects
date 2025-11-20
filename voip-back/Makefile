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
.PHONY: rebuild
rebuild: down build up info ## составная команда, для перезапуска контейнера [down -> build -> up -> info]
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
	docker-compose exec php php artisan passport:client --password --provider=employees --name='Employees'

.PHONY: info_domen
info_domen: ## Линки на сервисы сайта (для разных окружений)
	echo '----------------------------------------------------------------------------------------------------------------------------------------';
	printf "\\033[36m[x] LOCAL\\033[0m\\n";
	echo ${site};
	echo ${site}'/graphql-playground';
	echo ${site}'/telescope';
	echo ${site}':8025';
	echo ${site}'/api/v1/documentation';
	echo '----------------------------------------------------------------------------------------------------------------------------------------';
	printf "\\033[36m[x] TEST\\033[0m\\n";
	echo 'API         - https://api.voip-cooperandhunter.wezom.agency';
	echo 'ADMIN_PANEL - https://voip.ch.wezomteam.in.ua (admin@gmail.com, Password123)';
	echo 'TELESCOPE   - https://api.voip-cooperandhunter.wezom.agency/telescope';
	echo 'PLAYGROUND  - https://api.voip-cooperandhunter.wezom.agency/graphql-playground';
	echo 'SWAGGER  	  - https://api.voip-cooperandhunter.wezom.agency/api/v1/documentation';
	echo '----------------------------------------------------------------------------------------------------------------------------------------';
	printf "\\033[36m[x] PROD\\033[0m\\n";
	echo 'API         - https://back-voip.cooperandhunter.us';
	echo 'ADMIN_PANEL - https://voip.cooperandhunter.us/';
	echo 'TELESCOPE   - https://back-voip.cooperandhunter.us/telescope/SYCMbTn3vwAf2';
	echo 'PLAYGROUND  - https://back-voip.cooperandhunter.us/graphql-playground';
	echo 'SWAGGER  	  - https://back-voip.cooperandhunter.us/api/v1/documentation';
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
