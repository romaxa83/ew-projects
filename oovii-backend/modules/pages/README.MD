## Модуль Информационные страница (pages)

![State](https://img.shields.io/badge/state-start_develop-yellow)

<div id="pages-about"></div>
Модуль отвечает за создание информационных страниц , с переводами,
для мобильного приложения (в дальнейшем МП), на данные момент имеет три типа,
(для идентификации в МП)

* TYPE_PRIVATE_POLICY (политика конфидинциальности)
* TYPE_RULES (правило использования платформы)
* TYPE_AGREEMENT (пользовательское соглашение)

<p align="right">(<a href="https://bitbucket.org/wezom/oovii-backend/src/develop/#top">return to main</a>)</p>

[comment]: <> (## Installation)

[comment]: <> (#### Use console)

[comment]: <> (```bash)

[comment]: <> (composer require wezom-cms/pages)

[comment]: <> (```)

[comment]: <> (#### Or edit composer.json)

[comment]: <> (```json)

[comment]: <> ("require": {)

[comment]: <> (    ...)

[comment]: <> (    "wezom-cms/pages": "^8.1")

[comment]: <> (})

[comment]: <> (```)

[comment]: <> (#### Install dependencies:)

[comment]: <> (```bash)

[comment]: <> (composer update)

[comment]: <> (```)

[comment]: <> (#### Run migrations)

[comment]: <> (```bash)

[comment]: <> (php artisan migrate)

[comment]: <> (```)

[comment]: <> (## Publish)

[comment]: <> (#### Views)

[comment]: <> (```bash)

[comment]: <> (php artisan vendor:publish --provider="WezomCms\Pages\PagesServiceProvider" --tag="views")

[comment]: <> (```)

[comment]: <> (#### Lang)

[comment]: <> (```bash)

[comment]: <> (php artisan vendor:publish --provider="WezomCms\Pages\PagesServiceProvider" --tag="lang")

[comment]: <> (```)

[comment]: <> (#### Migrations)

[comment]: <> (```bash)

[comment]: <> (php artisan vendor:publish --provider="WezomCms\Pages\PagesServiceProvider" --tag="migrations")

[comment]: <> (```)
