## [7.0.1]
- Удалено публикация phpcs.xml и git pre-commit hook. Файл phpcs.xml можно скачать на bitbucket-е
- Добавлена возможность добавлять поля в поап редактирования изображения `ImageMultiUploaderAttachable`
- Добавлена инициализация упрощенного режима TinyMCE используя класс `js-tiny-wisywig`
- Добавлена авторизация и автоматическая регистрация роутов API

## [7.0]
- Изменён vendor с `wezom-laravel` на `wezom-cms`. В namespace так-же изменилось с `WezomLaravel` на `WezomCms`
- Публикация watermark и placeholders в `public` директорию, если их там нет
- По умолчанию подключены `ActionDeleteImage` и `ActionDeleteFile` в `AbstractCRUDController`. Роуты на удаление фото и файлов генерируются сразу при использовании `Route::adminResource()`.
- WebP копия фото сохраняется без сжатия
- Перемещены assets с корня модуля в `resources` директорию
- Доработан `PermissionsContainer`. Добавлены методы `withShow()`, `withSoftDeletes()`, `editSettings` и `withEditSettings()`
- AssetManager по умолчанию прописывает позицию `end_body` для `js`
- `FormBuilder::status()` автоматически определяет названия кнопок по названию поля. Поддерживаются поля: *active*, *read*, *published*.
- В фильтре для админ-панели убраны label теги, вместо них подписи прописываются в placeholder-ах
- Добавлена поддержка SoftDeletes на уровне AdminResourceRegistrar, Permissions, AbstractCRUDController, views
- Добавлен `ActionShowTrait`, который можно подключать в `AbstractCRUDController`-е для реализации просмотра объекта
- Изменены blade шаблоны админ-панели и blade директивы
- Исправлена ошибка регистрации роутов в админ-панель
- Исправлена ошибка по кешированию виджетов
- Генерация url для og:image без webP
- `AbstractCRUDController` автоматически вызывает метод `restoreSelectedOptions`, если он присутствует в фильтре
- Добавлена возможность массового восстановления SoftDeleted объектов
- Вынесена настройка per_page с формы поиска к блоку пагинации
- Восстанавливаемый фильтр в админ-панели
- Для форм созданы директивы `@langTabs` и `@tabs`. Директива `@langTabs` работает как компонент
- Исправлена ошибка восстановления локали в gotosite директиве
- TODO Аякс валидация форм, на лету

## [3.4]
- Перемещены фильтры моделей в `ModelFilters` директорию
- Реализовано автоопределение локали исходного текста в `TranslationDriver`
- Добавлен метод `afterSuccessfulSave` в `AbstractCRUDController` который вызывается при сохранении и обновлении модели
- В `AbstractCRUDController` удалены вызова методов `afterNotSuccessfulStore`, `afterNotSuccessfulUpdate`
- Переименован метод `AbstractCRUDController::afterSuccessfulDelete()` в `AbstractCRUDController::afterDelete()`
- `AbstractCRUDController` методы `store` и `update` теперь используют транзакции
- Front вынесен в модуль `wezom-cms/ui`
- Изменен механизм сохранения фото и файлов в `ImageAttachable` и `FileAttachable`. Теперь файлы сохраняются вручную,
без использования событий моделей и request-а
- Перемещены некоторые контроллеры в `Controllers` директорию. Те контроллеры, которые работают в админке - перемещены 
в `Admin` директорию
- Сделан специальный модуль `cli`, который может генерировать скелет модуля (`php artisan make:module`)
 и создавать справочник (`php artisan make:catalog Branch branches -m -f -s`)

## [3.3.1]
- Добавлен `Money` класс, функция `money()` и blade директива `@money()` для форматирования цены и вывода валюты
- Удалено сохранение оригиналных фото
- Переписан SettingStorage. Теперь используется одно хранилище
- В Setting добавлены методы для работы с файлами: `getFileUrl`, `getFileSize` и `getFileExtension`
- Реализована автоматическая генерация пути к представлению виджета на основе названия класса и пространства имен

## [3.3] - 10.02.2020
- `ImageAttachable`, `FileAttachable` и `Settings` используют `Storage`
- Добавлен макрос seo в миграциях для генерации seo полей: h1, title, keywords, description
- Поддержка в `ImageAttachable` загрузки изображения из строки (содержимого)
- Автоматическая геренация slug (алиаса) при создании объекта
- Автоматическое добавление поддержки видеоформатов в LFM
- Добавлена комманда `images:delete-lost`
- Разнесены Request-ы по директориях `Admin` & `Site`
- Добавлены правила валидации телефонов: `Phone`, `PhoneMask` & `PhoneOrPhoneMask`
- Исправлен баг с tooltip
- Добавлена модификация `RedirectIfAuthenticated` для поддержки `expectsJson` запросов
- Use inline `svg`
- Добавлена поддержка gif изображений
- В настройках добавлен `SeoFields` генератор полей
- Изменен тип данных для `primary` полей на `bigInteger`
- Переход на php7.3
- Избавились от использования `PaginateRoute`
- Микроразметка хлебных крошек генерируется отдельно в `ld+json` формате
- Добавлено событие `register_csrf_except_uri` для добавления в исключения `csrf` protection
- Сделана отдельная страница 404 для админ-панели

## [3.2] - 29.10.2019
- Администраторы могут включать или отключать уведомления
- Переименованы поля status в published, active, read
- Флаг публикации теперь не мультиязычный. Кроме: Новостей, Текстовых, Слайдера и меню
- Автоматическое подкидывание Enum ключей переводов. Нужно только прописать перечень Enum классов
- Перевод `AbstractCRUDController` на использование свойств вместо методов
- В Виджетах теперь метод `execute()` не обязательный. В этом методе и в конструкторе 
виджета можно использовать Dependency Injection
- Автоматизирована работа с мультиязычными полями в `PublishedTrait` и `GetForSelectTrait`
- В мультиязычных Request-ах используется `LocalizedRequestTrait`
- Во всех моделях сгенерирован новый DocBlock. + Используется `@mixin` на мультиязычную модель
- Автоматизирована работа `MultiLanguageSluggableTrait`
- Очищены и отформатированы свойства моделей. + Правильно прописаны поля в `$fillable` и `$translatedAttributes`
- В `UrlBuilderInterface` добавлен метод `first()`
- Добавлен метод `status` в `FormBuilder`
- Добавлена подержка кнопок в input-group Настройках сайта
- Добавлен хелпер SeoFields для генерации в настройках мета полей

## [3.1] - 25.09.2019
- Все вьюхи переименованы в kebab-case
- Добавлена поддержка массового удаления
- Почищены вьюхи фронта
- `view/emails` перемещешы по директориям `admin/notifications/email` & `site/notifications/email`
- добавлен поиск по меню админки
- все поля в БД с типом `text` изменены на `mediumText`
- добавлен импорт редиректов
- создан макрос регистрации ресурсных роутов с дефолтными екшинами: 'index', 'create', 'store', 'edit', 'update', 'destroy', 'massDestroy'
- копирование пунктов меню в другие позиции
- перевод фраз с помощью Google Translate (через терминал)
- `LanguageObserver`
- добавлена blade директива `@emptyResult` or `@emptyResult(__('result is empty'))`
- сделана микроразметка и наполнение OpenGraph тегов для новостей, услуг отзывов
- ключи виджетов переведены в cebab-case
- Добавлена директива редактирования сушности
`@edit(['obj'=> $obj, 'text' => str_limit($obj->question, 100), 'ability' => 'faq.edit', 'route' => 'admin.faq.edit']) - все параметры
@edit($obj, false) - вывод как кнопка btn
@edit($obj) - ссылка с анкором: $obj->name`
