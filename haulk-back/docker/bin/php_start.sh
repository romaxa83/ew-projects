#!/usr/bin/env bash

php artisan cache:clear
php artisan config:clear

composer install

php artisan key:generate
php artisan storage:link

#db container maybe is not start yet
php artisan migrate --no-interaction
php artisan db:seed
php artisan elastic:migrate

php artisan passport:keys

#php artisan passport:client --password --provider=admins --name='Admins'
#php artisan passport:client --password --provider=users --name='Users'

sleep 10

#ram volume not mounted yet
php artisan key:generate --env=testing
php artisan migrate --no-interaction --env=testing
php artisan db:seed --env=testing
php artisan passport:install --env=testing

exec php-fpm --nodaemonize
