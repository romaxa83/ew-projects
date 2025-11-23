.SILENT:

include .env

#=============================
# Variables

site := ${APP_URL}
php_container := ${APP_NAME}__php
nodejs_container := ${APP_NAME}__nodejs

UID := $(shell id -u)
GID := $(shell id -g)
#======================================
# Information on commands, called by the "make" command

.DEFAULT_GOAL := help
.PHONY: help
help:  ## отображение данного сообщения help
	@awk 'BEGIN {FS = ":.*##"; printf "\n Usage:\n  make \033[36m<command>\033[0m\n \n Targets:\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-10s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST)

#======================================
# Сompound commands

.PHONY: up
up: docker_up info ## составная команда, для поднятия проекта [docker_up -> test_init -> info]
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
	docker-compose build --build-arg USER_ID=${UID} --build-arg GROUP_ID=${GID}

.PHONY: docker_up
docker_up: ## подымает контейнеры
	docker-compose up -d

.PHONY: down
down: ## останавливает контейнеры и удаляет их образы
	docker-compose down --remove-orphans #очистит все запущенные контейнеры

.PHONY: ps
ps:	## информация по контейнерам докера
	docker-compose ps

.PHONY: ide_helper
ide_helper: ## генерация файла, для корректного отображение фасадов в ide
	docker-compose exec php php artisan ide-helper:generate
	docker-compose exec php php artisan ide-helper:models
	docker-compose exec php php artisan ide-helper:meta
	docker-compose exec php php artisan ide-helper:eloquent

.PHONY: perm
perm: ## задает права папкам (vendor, storage)
	sudo chmod 777 -R -f vendor/
	sudo chmod 777 -R -f storage

.PHONY: app_init
app_init: ## Инициализация ларавеловского приложения (запускается один раз)
	docker-compose exec php composer install
	docker-compose exec php php artisan key:generate
	#docker-compose exec php php artisan migrate
	#docker-compose exec php php artisan db:seed
	docker-compose exec php php artisan storage:link

.PHONY: front_init
front_init: ## Инициализация
	#docker-compose exec nodejs node -v
	#docker-compose exec nodejs npm -v
	docker-compose exec nodejs npm install

.PHONY: front_build
front_build: ## Билд фронта, для подтягивание изменений
	docker-compose exec nodejs npm ci
	docker-compose exec nodejs npm run production

.PHONY: info_domen
info_domen: ## Линки на сервисы сайта (для разных окружений)
	echo '----------------------------------------------------------------------------------------------------------------------------------------';
	printf "\\033[36m[x] LOCAL\\033[0m\\n";
	echo ${site}/login '(pavel.snowdog@gmail.com, 12345678)';
	echo ${site}':8025';
	echo ${site}'/log-viewer-wdpGBBBBlLS9sMjAfeDxgTHplwovTM5ePiKgJcHULnS1X1r33To9CVmNfz8Yb12Q';
	echo ${site}'/laravel-websockets';
	echo '----------------------------------------------------------------------------------------------------------------------------------------';
	printf "\\033[36m[x] TEST\\033[0m\\n";
	echo 'CRM    - https://h2hmovers.cmsdev.wezom.agency (pavel.snowdog@gmail.com, 12345678)';
	echo 'MOBILE - https://h2h-app.frontdev.wezom.agency/#/login (pavel.snowdog@gmail.com, 12345678)';
	echo 'LOG    - https://h2hmovers.cmsdev.wezom.agency/log-viewer-wdpGBBBBlLS9sMjAfeDxgTHplwovTM5ePiKgJcHULnS1X1r33To9CVmNfz8Yb12Q';
	echo '----------------------------------------------------------------------------------------------------------------------------------------';
	printf "\\033[36m[x] STAGE\\033[0m\\n";
	echo 'SITE   - https://stage.allymovers.com/';
	echo 'CRM    - https://stage-beta.allymovers.com/ (pavel.snowdog@gmail.com, 13qeadzc)';
	echo '----------------------------------------------------------------------------------------------------------------------------------------';
	printf "\\033[36m[x] PROD\\033[0m\\n";
	echo 'SITE   - https://allymovers.com';
	echo 'CRM    - https://beta.allymovers.com/';
	echo 'LOG    - https://beta.allymovers.com/log-viewer-wdpGBBBBlLS9sMjAfeDxgTHplwovTM5ePiKgJcHULnS1X1r33To9CVmNfz8Yb12Q';
	echo 'MOBILE - https://estimate.allymovers.com/';


#======================================
# Into container

.PHONY: php_bash
php_bash: ## зайти в контейнер php
	docker exec -it $(php_container) bash

.PHONY: nodejs_bash
nodejs_bash: ## зайти в контейнер node-js
	docker exec -it $(nodejs_container) sh

#======================================
# Test Command

.PHONY: test_init
test_init: ## настройка тестового окружения
	docker-compose run --rm php php artisan config:clear
	docker-compose run --rm php php artisan cache:clear
	docker-compose run --rm php php artisan key:generate -n --env=testing
	docker-compose run --rm php php artisan migrate -n --env=testing
	docker-compose run --rm php php artisan db:seed -n --env=testing

.PHONY: test_run
test_run: ## запускает тесты
	docker-compose exec php php artisan config:clear
	docker-compose exec php php artisan cache:clear
	docker-compose exec php php ./vendor/bin/phpunit

.PHONY: test_coverage_html
test_coverage_html: ## генерация отчета по тестам (html)
	docker-compose exec --env XDEBUG_MODE=coverage php php  ./vendor/bin/phpunit --coverage-html ./public/test/v-07/report

.PHONY: test_coverage_text
test_coverage_text: ## генерация отчета по тестам (text)
	docker-compose exec --env XDEBUG_MODE=coverage php php  ./vendor/bin/phpunit --coverage-text

.PHONY: user
user: ## user
	echo 'Current User UID: $(UID)';
	echo 'Current User GID: $(GID)';

.PHONY: list
list: ## list
	@ls -la;

