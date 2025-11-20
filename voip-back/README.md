<div id="top"></div>
# Backend для проекта VOIP

[![PHP](https://img.shields.io/badge/php-%5E8.0-blue)](https://www.php.net/)
[![Framework](https://img.shields.io/badge/laravel-8-red)](https://laravel.com/docs/8.x)
[![Database](https://img.shields.io/badge/postgres-14-green)](https://postgrespro.ru/docs/postgresql/14/index)
[![GraphQL](https://img.shields.io/badge/graphql-rebing-lightgrey)](https://github.com/rebing/graphql-laravel)
[![Cache](https://img.shields.io/badge/cache-redis-yellow)](https://redis.io/)
[![Octane](https://img.shields.io/badge/octane-1-black)](https://laravel.com/docs/8.x/octane)
[![Octane](https://img.shields.io/badge/telescope-true-white)](https://laravel.com/docs/8.x/telescope)

<!-- TABLE OF CONTENTS -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#about">О проекте</a>
    </li>
    <li>
        <a href="#deploy">Установка</a>
    </li>
    <li>
        <a href="#deploy">Тестирование</a>
    </li>
    <li>
        <a href="#code_style">Code style</a>
    </li>
  </ol>
</details>

<!-- ABOUT THE PROJECT -->
## О проекте
<div id="about"></div>


## Установка
<div id="deploy"></div>
Для локальной разработки:
- Копирование файлов окружения
```shell
./we init
```

- Правим файлы
```shell
nano .env
nano .env.testing
```

```shell
./we build
```

- Активируем паспорта клиентов
```shell
php artisan passport:client --password --provider=admins --name='Admins'
php artisan passport:client --password --provider=users --name='Users'
php artisan passport:client --password --provider=employees --name='Employees'
```

Вносим паспортные данные в .env
```shell
nano .env

#Заполняем из вывода предыдущих команд
OAUTH_USERS_CLIENT_ID=
OAUTH_USERS_CLIENT_SECRET=
OAUTH_ADMINS_CLIENT_ID=
OAUTH_ADMINS_CLIENT_SECRET=
OAUTH_EMPLOYEES_CLIENT_ID=
OAUTH_EMPLOYEES_CLIENT_SECRET=
```


## Code Style
<div id="code_style"></div>
<dl>
    <dt>Именованние классов мутаций</dt>
    <dd>
        Придерживаться такого именованния - < entity >< action > Mutation, <br> 
        пример:
        <ol>
            <li>UserCreateMutation</li>
            <li>UserDeleteMutation</li>
        </ol>
    </dd>
    <dt>Название mutation для фронта</dt>
    <dd>
        Называть в CamelCase - < entity >< action >  <br> 
        пример:
        <ol>
            <li>UserCreate</li>
            <li>ArticleDelete</li>
        </ol>
    </dd>
</dl>
