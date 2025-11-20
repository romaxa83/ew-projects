.SILENT:

include .env
#======================================
# Variables

site = http://192.168.144.1
php_container = ${APP_NAME}__php-fpm

#======================================
# Main Command

up: docker_up info
restart: down build up info
init: build docker_up app_init permission info
test: test_init test_run

#======================================
# Command

build:
	docker-compose build

docker_up:
	docker-compose up -d

down:
	docker-compose down --remove-orphans #очистит все запущеные контейнеры

.PHONY: ps
ps:
	docker-compose ps

.PHONY: info
info: ps info_domen

cp_env:
	cp .env.example .env

permission:
	sudo chmod 777 -R -f vendor/
	sudo chmod 777 -R -f docker/storage
	sudo chmod 777 -R -f storage

app_init:
	docker-compose exec php-fpm composer install
	docker-compose exec php-fpm php artisan key:generate
	docker-compose exec php-fpm php artisan migrate
	docker-compose exec php-fpm php artisan db:seed
	docker-compose exec php-fpm php artisan jd:create:admin c
	docker-compose exec php-fpm php artisan ide-helper:generate
	docker-compose exec php-fpm php artisan ide-helper:meta
	docker-compose exec php-fpm php artisan passport:install
	docker-compose exec php-fpm php artisan jd:import:all
	docker-compose exec php-fpm php artisan jd:fill-feature

info_domen:
	echo '---------------------------------';
	echo '----------DEV--------------------';
	echo ${site};
	echo ${site}'/api/documentation';
	echo ${site}':8025';
	echo ${site}'/test/v-07/report/index.html';
	echo '---------------------------------';
	echo '----------STAGE------------------';
	echo 'http://jddemofront.wezom.agency/';
	echo 'http://jddemo.wezom.agency/api/documentation';
	echo 'http://jddemo.wezom.agency/test/v-07/report/index.html';
	echo '---------------------------------';
	echo '----------PRODUCT----------------';
	echo 'https://jd-demonstration.com/';
	echo 'https://api.jd-demonstration.com/api/documentation';

api_docs:
	docker-compose exec php-fpm php artisan l5-swagger:generate
	sudo chmod 777 -R storage/api-docs

#======================================
# Into container

php_bash:
	docker exec -it $(php_container) bash

db_bash:
	docker exec -it $(db_container) bash

#======================================
# Test Command

test_init:
	docker-compose run --rm php-fpm php artisan key:generate -n --env=testing
	docker-compose run --rm php-fpm php artisan migrate -n --env=testing
	docker-compose run --rm php-fpm php artisan cmd:helpers-to-csv
	docker-compose run --rm php-fpm php artisan cmd:set-data -n --env=testing

test_run:
	docker-compose exec php-fpm php ./vendor/bin/phpunit
	#docker-compose exec $(php_container) php ./vendor/bin/phpunit

test_report:
	docker-compose exec --env XDEBUG_MODE=coverage php-fpm php  ./vendor/bin/phpunit --coverage-html ./public/test/v-07/report
	#docker-compose exec --env XDEBUG_MODE=coverage $(php_container)  php  ./vendor/bin/phpunit --coverage-html ./public/test/v-07/report
