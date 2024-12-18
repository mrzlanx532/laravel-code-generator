# Laravel Code Generator

## Установка 
`composer require --dev mrzlanx532/laravel-code-generator`

## Команды генерации кода
### Создать модель
`php artisan mrzlanx532:make_model <название_таблицы> <неймспейс_с_названием_класса_модели>`

Пример:  
`php artisan mrzlanx532:make_model market_offers_offers \\App\\Models\\Market\\Offer\\Offer`

### Создать ресурс
`php artisan mrzlanx532:make_resource <неймспейс_с_названием_класса_модели> <неймспейс_с_названием_класса_ресурса>`

Пример:  
`php artisan mrzlanx532:make_resource \\App\\Models\\User \\App\\Http\\Resources\\UserResource`

### Создать сервис

1. `php artisan mrzlanx532:make_service --create <неймспейс_с_названием_класса_модели> <неймспейс_с_названием_класса_сервиса>`  
2. `php artisan mrzlanx532:make_service --update <неймспейс_с_названием_класса_модели> <неймспейс_с_названием_класса_сервиса>`  
3. `php artisan mrzlanx532:make_service --delete <неймспейс_с_названием_класса_модели> <неймспейс_с_названием_класса_сервиса>`  

Пример:

1. `php artisan mrzlanx532:make_service --create \\App\\Models\\User \\App\\Http\\Resources\\UserCreateService`  
2. `php artisan mrzlanx532:make_service --update \\App\\Models\\User \\App\\Http\\Resources\\UserUpdateService`  
3. `php artisan mrzlanx532:make_service --delete \\App\\Models\\User \\App\\Http\\Resources\\UserDeleteService`  