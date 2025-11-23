<?php

use WezomCms\Core\Enums\TranslationSide;

return [
    TranslationSide::ADMIN => [
        'Product review' => 'Отзыв о товаре',
        'Product reviews' => 'Отзывы о товарах',
        'Reviews list' => 'Список отзывов',
        'Product' => 'Товар',
        'Answer for' => 'Ответ на',
        'Name' => 'Имя',
        'Date' => 'Дата',
        'Create new review' => 'Создать новый отзыв',
        'E-mail' => 'E-mail',
        'Text' => 'Текст',
        'Short text' => 'Краткий текст',
        'Created at' => 'Дата отзыва',
        'Site reviews limit at product page' => 'Лимит отзывов на карточке товара',
        'Product reviews no published' => 'Новые отзывы о товарах',
        'Site administration' => 'Администрация сайта',
        'export' => [
            'Product id' => 'ID товара',
            'Product name' => 'Название товара',
            'User name' => 'Имя пользователя',
            'User email' => 'E-mail пользователя',
            'Like/Dislike' => 'Лайк/Дізлайк',
            'Text' => 'Текст',
        ],
        'email' => [
            'Callback' => 'Обратный звонок',
            'Name' => 'Имя',
            'E-mail' => 'E-mail',
            'Product' => 'Товар',
            'Date' => 'Дата',
            'Rating' => 'Рейтинг',
            'Orders list' => 'Список заказов',
            'New product review' => 'Новый отзыв на товар',
            'New product review answer' => 'Новый комментарий к отзыву',
            'Created at' => 'Отправлено',
            'Go to admin panel' => 'Перейти в админ-панель',
            'Text' => 'Текст',
        ],
        'Parent review' => 'Родительский отзыв',
        'Rating' => 'Оценка',
        'Already bought' => 'Уже купил',
        'Admin answer' => 'Ответ администратора',
        'Like' => 'Лайк/Дизлайк',
        'Likes' => 'Лайки',
        'Dislikes' => 'Дизлайки',
        'The attribute field is required unless' => 'Поле :attribute обязательное если не :other',
        'The attribute field is required when admin answer' => 'Поле :attribute обязательное если это :other',
        'Write answer' => 'Ответить',
        'Comment rules page' => 'Страница с правилами комментирования',
        'validation' => [
            'like' => [
                'required' => 'Лайк/дизлайк обязателен'
            ],
            'text' => [
                'required' => 'Текст отзыва обязателен'
            ],
            'parent_id' => [
                'exists' => 'Нет родительского отзыва'
            ],
        ],
    ],
    TranslationSide::SITE => [
        'Name' => 'Имя',
        'Product' => 'Товар',
        'Form successfully submitted!' => 'Форма успешно отправлена!',
        'Error creating request!' => 'Ошибка создания заявки!',
        'Text' => 'Комментарий',
        'ratings' => [
            1 => 'Очень плохой',
            2 => 'Плохой',
            3 => 'Средний',
            4 => 'Хороший',
            5 => 'Отличный',
        ],
        'sort_variants' => [
            'latest' => 'Самые новые',
            'oldest' => 'Самые старые',
            'top' => 'Популярные',
        ],
        'Rating' => 'По рейтингу',
        'My reviews' => 'Мои отзывы',
    ],
];
