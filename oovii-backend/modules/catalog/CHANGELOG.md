# [3.3.2]
- Изменен механизм объединения товаров
- Разгружена модель `Product` некоторые методы удалены, некоторые вынесены в трейты, некоторые во `ViewModel`

# [3.3.1]
- Добавлены шаблоны изображений для товара
- Удалены product getter-ы `formatted_*`, `formatted_*_with_currency`
- Подключено `spatie/laravel-view-models` для `ProductController`, `CategoryController` и `SearchController`
