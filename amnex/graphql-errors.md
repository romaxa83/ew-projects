
## Види класифікацій помилок

* BAD_REQUEST
* UNAUTHORIZED
* FORBIDDEN
* NOT_FOUND
* INTERNAL_ERROR


### BAD_REQUEST

Виникає, коли передані дані не коректні. Зачасту це валідація даних.

#### Exceptions

* ValidationException
* TODO GraphQlValidationException

```json
{
    "errors": [
        {
            "message": "some message",
            "locations": [],
            "path": [],
            "extensions": {
               "classification": "BAD_REQUEST",
               "validation": {
                   "field": [
                       "error message"
                   ]
               }
            }
        }
    ]
}
```

### UNAUTHORIZED

Виникає, коли не передано Authorization токен, або він не валідний (протух)

#### Exceptions

* AuthenticationException

```json
{
    "errors": [
        {
            "message": "Unauthorized",
            "locations": [],
            "path": [],
            "extensions": {
               "classification": "UNAUTHORIZED"
            }
        }
    ]
}
```

### FORBIDDEN

Виникає, коли передано валідний Authorization токен, але у користувача недостатньо прав для виконання запиту (відсутній доступ)

#### Exceptions

* AuthorizationException
* UnauthorizedException

```json
{
    "errors": [
        {
            "message": "Access Denied",
            "locations": [],
            "path": [],
            "extensions": {
               "classification": "FORBIDDEN"
            }
        }
    ]
}
```

### NOT_FOUND

Виникає, коли не знайдено сутності за переданим ідентифікатором. Схоже на 404 помилку.

#### Exceptions

* ModelNotFoundException

```json
{
    "errors": [
        {
            "message": "Entity 'TagEntity' not found by id '123'",
            "locations": [],
            "path": [],
            "extensions": {
               "classification": "NOT_FOUND"
            }
        }
    ]
}
```

### INTERNAL_ERROR

Виникає, коли відбулась помилка на сервері або потрібно показати текст помилки користувачу.

#### Exceptions

* TranslatedException
* TODO Exception

```json
{
    "errors": [
        {
            "message": "Текст повідомлення, який потрібно показати в снекбар-і. Може бути зрозумілим текстом з перекладом, а може бути Server Error. Виводити завжди.",
            "locations": [],
            "path": [],
            "extensions": {
               "classification": "INTERNAL_ERROR"
            }
        }
    ]
}
```