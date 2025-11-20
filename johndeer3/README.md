<div id="top"></div>
## Backend John Deere Demonstration

[![PHP](https://img.shields.io/badge/php-%5E7.2-blue)](https://www.php.net/)
[![Framework](https://img.shields.io/badge/laravel-6.2-red)](https://laravel.com/docs/8.x)
[![Database](https://img.shields.io/badge/mysql-5.7-green)](https://dev.mysql.com/doc/refman/5.7/en/)


<div id="about"></div>
Бэкенд для мобильного приложения (в дальнейшем МП) и адинистративной панели (в дальнейшем МП).
Суть проекта, создание отчетов (МП) по демонстрации агр. техники в полевых условиях для потенциальных
клиентов.А также администрирования отчетов и составлении статистика на основе полученых данных

<div id="deploy"></div>
Настраиваем переменные окружение, создаем env-файл и заполняем его настройками

```sh
$ cp .env.example .env
$ cp .env.testing.example .env.testing  # для тестов
```

```dotenv
# Docker (для локальной разработки), ip для локальной сети сервисов
DOCKER_BRIDGE=192.168.175.1
DOCKER_NETWORK=192.168.175.0/24

#Настройки swagger второй версии
SWAGGER_VERSION=2.0
L5_SWAGGER_GENERATE_ALWAYS=true

# авторизационые данные
OAUTH_SECRET_KEY=N72qtu6oHQIT4nznFGKH3sK2FfIrZWa1ktw0mdje
OAUTH_SECRET_ID=2

# подключения к основному проекту, для получение данных
JOHN_DEER_BASE_URL=http://testjohndeereapi.wezom.agency
JOHN_DEER_API_KEY=base64:fiS6wPGbNKA7fiv+UmfOI7OBE8oAGSVea/d10JQs1eE=

RANDOM_PASSWORD=true
#Telegram (для разработки)
TELEGRAM_ENABLE=false
TELEGRAM_ENV=local
TELEGRAM_TOKEN=
TELEGRAM_CHAT_ID=

# Настроийки firebase, для нотификации
ENABLE_FIREBASE=true
FIREBASE_SERVER_KEY=
FIREBASE_SENDER_ID=
FCM_SEND_URL=

# Ссылка на приложение Google market
ANDROID_LINK=https://play.google.com/store/apps
```

<div id="testing"></div>
Настройка и запуск тестов

```sh
# копируем и заполняем файл настроек
$ cp .env.testing.dist .env.testing
```

```sh
# последовательно запускает команды ниже
$ cp make test

# настраивает тестовую среду
$ cp make test_init
# запускает тесты
$ cp make test_run
```
