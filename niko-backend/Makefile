
.SILENT:

include .env

#=============VARIABLES================
php_container = ${APP_NAME}__php-fpm
node_container = ${APP_NAME}__node
db_container = ${APP_NAME}__db
redis_container = ${APP_NAME}__redis
#======================================

#=====MAIN_COMMAND=====================

up: up_docker info

rebuild: down build up_docker info

up_docker:
	docker-compose up -d

down:
	docker-compose down --remove-orphans

# флаг -v удаляет все volume (очищает все данные)
down-clear:
	docker-compose down -v --remove-orphans

build:
	docker-compose build

ps:
	docker-compose ps

run_test:
	docker-compose run --rm php-fpm ./vendor/bin/phpunit

#=======COMMAND_FOR_APP================

app-init: composer-install project-init perm

composer-install:
	docker-compose run --rm php-fpm composer install

supervisor:
	docker-compose run --rm php-fpm service supervisor start
	docker-compose run --rm php-fpm supervisorctl reread
	docker-compose run --rm php-fpm supervisorctl update
	docker-compose run --rm php-fpm supervisorctl status


project-init:
	docker-compose run --rm php-fpm php artisan key:generate
	docker-compose run --rm php-fpm php artisan ide-helper:generate
	docker-compose run --rm php-fpm php artisan ide-helper:meta
	docker-compose run --rm php-fpm php artisan migrate
	docker-compose run --rm php-fpm php artisan db:seed
#	docker-compose run --rm node npm install
#	docker-compose run --rm node npm run dev

perm:
	sudo chmod 777 -R storage

#=======INTO_CONTAINER=================

php_bash:
	docker exec -it $(php_container) bash

node_bash:
	docker exec -it $(node_container) sh

db_bash:
	docker exec -it $(db_container) sh

redis_bash:
	docker exec -it $(redis_container) sh
#=======FOR_TEST=================

test_app_init:
# 	docker-compose run --rm php-fpm php artisan key:generate --env=testing -n
	docker-compose run --rm php-fpm php artisan migrate -n --env=testing
#=======INFO===========================

info:
	echo ${APP_URL}/wezom;
