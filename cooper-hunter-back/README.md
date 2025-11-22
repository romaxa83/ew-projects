## Установка

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

- Билдим контейнера

```shell
./we build
```

- Активируем паспорта клиентов

```shell
php artisan passport:client --password --provider=admins --name='Admins'
php artisan passport:client --password --provider=users --name='Users'
php artisan passport:client --password --provider=1c_moderators --name='1CModerators'
php artisan passport:client --password --provider=technicians --name='Technicians'
```

Вносим паспортные данные в .env

```shell
nano .env

#Заполняем из вывода предыдущих команд
OAUTH_USERS_CLIENT_ID=
OAUTH_USERS_CLIENT_SECRET=
OAUTH_ADMINS_CLIENT_ID=
OAUTH_ADMINS_CLIENT_SECRET=
OAUTH_TECHNICIANS_CLIENT_ID=
OAUTH_TECHNICIANS_CLIENT_SECRET=

OAUTH_1C_CLIENT_ID=
OAUTH_1C_CLIENT_SECRET=
```

Firebase

```shell

#Необходимо указать название файла доступами к firebase
#https://console.firebase.google.com/project/_/settings/serviceaccounts/adminsdk
FIREBASE_CREDENTIALS=
FIREBASE_API_URL=https://fcm.googleapis.com/v1/projects/%s/messages:send
FIREBASE_PROJECT=
```

## Создание менеджеров системы

### Администратор

```shell
php artisan admin:create
```

### Менеджер 1С

```shell
php artisan moderator:create
```
