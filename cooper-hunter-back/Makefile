.SILENT:

include .env

#=============================
# Variables

site = ${APP_URL}
php_container = ${APP_NAME}_php

#======================================
# Main Command

up: docker_up info
restart: down build up info
init: down build docker_up info
test: test_init test_run

#======================================
# Command

build:
	docker-compose build

docker_up:
	docker-compose up -d

down:
	docker-compose down --remove-orphans #очистит все запущеные контейнеры

ps:
	docker-compose ps

info: ps info_domen

perm:
	sudo chmod 777 -R -f vendor/
	sudo chmod 777 -R -f storage


info_domen:
	echo '---------------------------------';
	echo '[x] DEV -------------------------';
	echo ${site};
	echo ${site}'/graphql-playground';
	echo ${site}'/telescope';
	echo ${site}':8025';
	echo ${site}'/docs/index.html';
	echo '---------------------------------';
	echo '[x] TEST ------------------------';
	echo 'ADMIN-PANEL - https://cooperhunter.wezomteam.in.ua/';
	echo 'SITE        - https://cooperhunter-front.wezomteam.in.ua/';
	echo 'https://cooperhunter.wezom.agency/graphql-playground';
	echo 'https://cooperhunter.wezom.agency/telescope';
	echo 'https://cooperhunter.wezom.agency/docs/index.html';
	echo '---------------------------------';
	echo '[x] STAGE -----------------------';
	echo 'ADMIN-PANEL - https://admin.stage.cooperandhunter.us';
	echo 'SITE        - https://front.stage.cooperandhunter.us';
	echo 'TELESCOPE   - https://api.stage.cooperandhunter.us/telescope';
	echo '---------------------------------';
	echo '[x] PROD -----------------------';
	echo 'ADMIN-PANEL - https://admin.cooperandhunter.us';
	echo 'SITE        - https://cooperandhunter.us';
	echo 'API DOCS    - https://api.cooperandhunter.us/docs/index.html';
	echo 'TELESCOPE   - https://api.cooperandhunter.us/telescope/SYCMbTn3vwAf2/requests';
	echo '---------------------------------';

#======================================
# Into container

php_bash:
	docker exec -it $(php_container) bash

#======================================
# Test Command

test_init:
	docker-compose run --rm php php artisan key:generate -n --env=testing
	docker-compose run --rm php php artisan migrate -n --env=testing

test_run:
	docker-compose exec php php ./vendor/bin/phpunit

test_coverage:
	docker-compose exec --env XDEBUG_MODE=coverage php php  ./vendor/bin/phpunit --coverage-html ./public/test/v-07/report
