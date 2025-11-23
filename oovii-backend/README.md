<div id="top"></div>
## Backend Oovii project

[![PHP](https://img.shields.io/badge/php-%5E8.0-blue)](https://www.php.net/)
[![Framework](https://img.shields.io/badge/laravel-8-red)](https://laravel.com/docs/8.x)
[![Database](https://img.shields.io/badge/mysql-8-green)](https://dev.mysql.com/doc/refman/8.0/en/)
[![Database](https://img.shields.io/badge/cms-7-blue)](https://bitbucket.org/wezom/workspace/projects/CMS7)
[![Cache](https://img.shields.io/badge/cache-redis-yellow)](https://redis.io/)

<!-- TABLE OF CONTENTS -->
<details>
  <summary>Documentations</summary>
  <ol>
    <li><a href="#lib">Используемые библиотеки</a></li>
    <li><a href="#deploy">Разворачивание проекта</a></li>
    <li>
        <a href="#deploy">Модули</a>
        <ul>
            <li><a href="https://bitbucket.org/wezom/oovii-backend/src/develop/modules/users/README.MD">Пользователи (users)</a></li>
            <li><a href="https://bitbucket.org/wezom/oovii-backend/src/develop/modules/providers/README.MD">Поставщики (providers)</a></li>
            <li><a href="https://bitbucket.org/wezom/oovii-backend/src/develop/modules/catalog/README.MD">Каталог товаров (catalog)</a></li>
            <li><a href="https://bitbucket.org/wezom/oovii-backend/src/develop/modules/pages/README.MD">Инф. страницы (pages)</a></li>
            <li><a href="https://bitbucket.org/wezom/oovii-backend/src/develop/modules/translates/README.MD">Переводы (translates)</a></li>
            <li><a href="https://bitbucket.org/wezom/oovii-backend/src/develop/modules/sms-verify/README.md">SMS верификация (sms-verify)</a></li>
        </ul>
    </li>
  </ol>
</details>


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

# включение api (требует cms-7)
API_ENABLED=true

# авторизационые данные для пользователя, сгенерированые
# командой passport:client --password --provider=users --name='Users'
OAUTH_USERS_ID=1
OAUTH_USERS_SECRET=tYKNcqX6jFOqAwgCkgBH72sU9hrSi19qpi3rCWiL

# настройки бота, для разработки и наблюдения за ошибками
TELEGRAM_USE=
TELEGRAM_ENV=
# for send message [allowed values - info/important/critical ]
TELEGRAM_LEVEL=
TELEGRAM_TOKEN=
TELEGRAM_CHAT_ID=

TELESCOPE_ENABLED=
```

Локально развернуть через docker
```sh
$ make init    # развернуть проект (выполняется один раз, вначале) 
$ make up      # поднять сервисы
$ make down    # остановить сервисы
$ make rebuild # перестроить сервисы
$ make info    # информация по проекту
$ make test    # запуск тестового окружения
```
<p align="right">(<a href="#top">back to top</a>)</p>


<div id="lib"></div>
#### Используемые библиотеки

Swagger - апи документация (
<a href="https://github.com/DarkaOnLine/L5-Swagger">repo</a>
<a href="https://github.com/zircote/swagger-php/tree/master/Examples"> | example</a>
<a href="https://zircote.github.io/swagger-php/Getting-started.html#document-your-code-using-annotations-or-php-attributes"> | docs</a>
)

Passport - авторизация для api (
<a href="https://laravel.com/docs/8.x/passport">docs</a>
)

Laravel excel - генерация excel документов (
<a href="https://docs.laravel-excel.com/3.1/getting-started/">docs</a>
)

Telescope (
<a href="https://laravel.com/docs/8.x/telescope">docs</a>
)
<p align="right">(<a href="#top">back to top</a>)</p>
