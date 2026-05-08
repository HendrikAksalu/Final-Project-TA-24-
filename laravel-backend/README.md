# Fototeek — Laravel REST API

See kaust (`laravel-backend/`) sisaldab **Fototeegi** backendi: **Laravel 13** REST API-d, mis teenindab Vue 3 SPA-d.

- **Autentimine**: Laravel Sanctum (isikliku juurdepääsuloa token, `Authorization: Bearer <token>`)
- **API marsruudid**: `routes/api.php`
- **Piltide salvestus**: `storage/app/public/memories/` (ja `public/storage` symlink)
- **Andmebaas**: SQLite

## Arendus

Täielik kiirkäivitus ja keskkonnamuutujad on kirjeldatud repo juur-README-s:

- Vaata `../README.md` → **“Kiirkäivitus (soovituslik arendus)”**

## Kasulikud käsud

```bash
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan storage:link
php artisan serve
```

## Testid ja stiil

```bash
php artisan test
./vendor/bin/pint
```
