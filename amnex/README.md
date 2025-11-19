## Wezom CMS starter

### Requirements

* Git
* Docker Engine (v24 or higher)
* Docker compose (v2.20 or higher)

## Getting Started

### Linux users

Додай псевдонім до файлу `we`, якщо ще не додавав:

```bash
# ~/.bash_aliases
alias we="./we"
```

Зроби файл `we` виконуючим:
```bash
chmod +x we
```

Досліди файл `we` на наявність доступних команд:
```bash
we help
```

### Mac users

Файл `we` запускається через термінал командою:
```bash
./we [command]
```

### Перший запуск

* Внести необхідні зміни в оточення: `.env` та `.env.testing`

> **Порада:** Змінна `APP_NAME=` має бути без пробілів.  
> Замість пробілів та інших знаків необхідно використовувати нижнє підкреслення `_`  
> Не бажано використовувати знак `-`  
> Найкраще використовувати camelCase

> Це необхідно для правильного налаштування докер контейнерів та конфігурації IDE для запуску тестів в середині 
> запущених контейнерів.

> Змінюй змінні `DOCKER_NETWORK` та `DOCKER_ADDRESS` одночасно, наприклад:
```dotenv
# попередній проєкт:
# DOCKER_NETWORK=192.168.100.0/24
# DOCKER_ADDRESS=192.168.100.1
# тоді, наступний проєкт матиме такі налаштування
DOCKER_NETWORK=192.168.101.0/24
DOCKER_ADDRESS=192.168.101.1
```
> Це дозволить запускати одразу багато проєктів паралельно.

Для MacOS потрібно прописати наступні змінні:
```dotenv
# лише в .env
DOCKER_NETWORK=127.0.0.0/24
DOCKER_ADDRESS=127.0.0.1
# .env та .env.testing
DB_HOST="host.docker.internal"
```
* Відредагуй в `Dockerfile` та в `DockerfileXdebug` назви базового контейнера:
```
#Dockerfile
FROM {APP_NAME}-base

#DockerfileXdebug
FROM {APP_NAME}-php
```
>Де {APP_NAME} - це назва з .env

* Запустити команду `we init` та виконати інструкції

* Створити образи докера:
```bash
we build
```

### Важливо

Такі змінні як: `APP_NAME`, `DOCKER_NETWORK` та `DOCKER_ADDRESS` в оточеннях `.env` та `.env.testing` мають 
бути однаковими в поточному проєкті, але НЕ пересікатись з іншими проєктами.

### Налаштування IDE

#### PHP Interpreter
Після того, як контейнери докера збудуються, можна налаштувати інтерпретатор PHP із контейнера

1. Settings -> PHP -> Cli interpreter -> '...' -> '+' -> From docker, Vagrant, VM -> Docker Compose
2. Services -> обрати: `php` (для запуску тестів без xDebug)
3. Services -> обрати: `php_xdebug` (для запуску тестів з xDebug)
4. Lifecycle -> Connect to existing container
5. Environment variables -> вставити значення `COMPOSE_PROJECT_NAME=your_app_name`

> Тут `your_app_name` - це значення `APP_NAME` з оточення `.env`  
> Дивись пораду з пункту "Перший запуск"

#### PHPUnit
1. Settings -> PHP -> Test Frameworks -> '+' -> PHPUnit by remote interpreter -> select interpreter
2. select interpreter -> `php` or `php_xdebug`

> За замовчуванням краще обрати `php` оскільки `php_xdebug` набагато повільніший.
> А у разі необхідності додати `php_xdebug` (виконавши перший пункт з вибором `php_xdebug`) та запускати необхідний тест з дебагом

#### Git modules
Іноді буває, що після встановлення модуля, IDE не відв'язує маппінг вкладеного git репозиторію.  
Це можна поправити вручну:

`Settings -> Version Control -> Directory Mappings`

Далі видалити всі гіт директорії, які знаходяться в середині директорії `modules`

### Встановлення модулей

Розберемо встановлення модулей на прикладі модуля `core`

#### Встановлення через module installer util

Встановлення модуля відбувається в декілька етапів:

1. Запустити команду `./we install core`
    > Примітка: повну назву модуля (наприклад core_module) писати не обов'язково.
Завантаження модуля відбудеться в директорію з назвою модуля, без суфікса "_module"
   
2. Додай до `composer.json` в розділ `autoload-dev` неймспейс до тестового середовища:
    ```json
    {
      "autoload-dev": {
        "psr-4": {
          "Wezom\\Core\\Tests\\": "modules/core/tests"
        }
      }
    }
    ```

3. Запустити команду `we composer require wezom/core:^1.0`

#### Ручне встановлення модулей

1. `cd modules`
2. `git clone git@bitbucket.org:wezom/core_module.git core --branch {target_branch}`
3. `cd core`
4. **Важливо** `rm -rf .git`
5. Виповнити кроки 2 і 3 з **Встановлення через module installer util**
