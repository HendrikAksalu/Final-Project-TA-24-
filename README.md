# Fototeek — fotoalbum peredele mälestuste kogumiseks

**Fototeek** on veebirakendus, mis aitab peredel ja suguvõsadel ühiselt fotosid ja mälestusi talletada. Rakenduses saab luua albumeid, **lisada pilte**, **jagada albumeid teiste kasutajatega** ning hallata oma kasutajakontot (profiil ja salasõna).

**Frontend** on Vue 3 üheleherakendus (SPA), mis suhtleb **Laravel REST API** kaudu; autentimine toimib **Laravel Sanctumi** isikliku juurdepääsuloa tokeniga (`Bearer`), mitte Inertia sessiooniga.

---

## Monorepo: miks backend ja frontend on ühes repositooriumis

Projekt on korraldatud **monorepo** põhimõttel: Laravel asub kaustas `laravel-backend/`, Vue SPA repo **juurkaustas** (`src/`, juurekausta `vite.config.js`).

### Miks see valik sobib sellele rakendusele

- **Üks checkout** — õppeprojekti ja väikese tiimi jaoks piisab ühest `git clone` käsust; API ja UI muutuvad koos ühes PR-is.
- **Versioonid sobivad kokku** — sama commit kirjeldab nii API kui SPA käitumist.
- **Juurutus** — üks repo hoiab deploy seadistuse, backendi ja frontendi ühes kohas.

### Plussid ja miinused

| Pluss | Lühikirjeldus |
|--------|----------------|
| Aatomilised muudatused | Üks PR võib hõlmata migratsiooni, API ja Vue muudatust. |
| Ühtne versioon | `main` / tag kirjeldab kogu rakenduse seisu. |
| Lihtsam sisseelamine | Selge jaotus: `laravel-backend/` = API, juur = SPA. |

| Miinus | Praktiline tähendus |
|--------|---------------------|
| Kaks töövoogu | Arendaja käivitab nii `composer`/`artisan` kui juurekausta `npm run dev`. |
| Mahukas lokaalselt | `vendor/` ja `node_modules/` kaustad on suured. |
| CORS ja API baas-URL | SPA jaoks vaja `VITE_API_BASE_URL` ja Laravelis `FRONTEND_URL`. |

---

## Tehnoloogiad

| Kiht | Tehnoloogia |
|------|--------------|
| Backend | PHP ^8.3 · Laravel ^13 · Laravel Sanctum |
| Frontend (SPA) | Vue ^3.5 · Vue Router ^4 · Vite ^7 |
| API | JSON REST (`/api/...`) · token autentimine |
| Andmebaas | SQLite (ainus toetatud andmebaas) |
| Testid (backend) | PHPUnit (`php artisan test`) · Laravel Pint |

---

## Repo struktuur

```text
Final-Project-TA-24-/
├── .github/workflows/      # GitHub Actions (CI + Production deploy)
├── laravel-backend/        # Laravel 13 REST API
│   ├── app/Http/Controllers/   # AlbumController, MemoryController, AuthController jt
│   ├── database/migrations/    # Andmebaasi migratsioonid
│   ├── routes/api.php          # API marsruudid
│   └── storage/app/public/memories/  # Üleslaaditud pildid (jagatud kaust)
├── public/                 # Vue SPA staatilised assetid
├── scripts/                # Juurutamise abiskriptid (setup-shared-sqlite.sh)
├── src/                    # Vue 3 SPA lähtekood
│   ├── api/fototeekApi.js      # API päringute klient
│   ├── pages/                  # Lehed (HomePage, AlbumPage, MemoryPage jt)
│   └── utils/imageResize.js    # Pildi töötlus enne üleslaadimist
├── deploy.php              # Deployer konfiguratsioon
└── vite.config.js          # Vite ehitustööriist
```

---

## Eeltingimused

- **PHP 8.3+** ja **Composer 2+**
- **Node.js** (soovituslik **20.19+**, vt `package.json` `engines`)
- **SQLite** (ainus toetatud andmebaas)

---

## Kiirkäivitus (soovituslik arendus)

Kaks protsessi: API server ja Vue arendusserver.

### 1) Laravel API (`laravel-backend`)

```bash
cd laravel-backend
cp .env.example .env
php artisan key:generate
```

Sea `.env` faili vähemalt need lokaalsed väärtused:

```env
APP_NAME=Fototeek
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
FRONTEND_URL=http://localhost:5173
SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173
DB_CONNECTION=sqlite
```

Loo SQLite fail, käivita migratsioonid ja storage symlink:

```bash
touch database/database.sqlite
php artisan migrate
php artisan storage:link
```

Käivita API:

```bash
php artisan serve
```

> NB! Pildi töötlemiseks (resize, thumbnail) on vaja PHP GD laiendust. Ubuntu/WSL: `sudo apt install php-gd`. Zone.ee tootmiskeskkonnas on GD vaikimisi olemas.

### 2) Vue SPA (repo juur)

```bash
cd ..   # repo juur
cp .env.example .env
# Vaikimisi: VITE_API_BASE_URL=http://127.0.0.1:8000
npm install
npm run dev
```

Ava brauseris **`http://127.0.0.1:5173`** (või Vite väljastatud port). API päringud lähevad `VITE_API_BASE_URL` alla.

---

## Keskkonnamuutujad (lühike kokkuvõte)

| Fail | Mõte |
|------|------|
| `laravel-backend/.env` | `APP_URL`, `FRONTEND_URL`, `DB_CONNECTION=sqlite`, `APP_KEY` |
| Repo juur `.env` | `VITE_API_BASE_URL` — sama host/port, kuhu Laravel API päringud lähevad |

---

## Kasulikud käsud

```bash
# Vue SPA (juur)
npm run dev
npm run build
npm run preview

# Laravel (laravel-backend)
php artisan migrate
php artisan test
./vendor/bin/pint
```

---

## API marsruudid (ülevaade)

Autenditud kasutajale (Bearer token): albumid, mälestused, jagamine, `GET/PATCH /api/user`, salasõna `PATCH /api/user/password` jne.

Täieliku loendi näed:

```bash
cd laravel-backend && php artisan route:list
```

---

## Juurutamine

Juurutamine on automatiseeritud Deployer + GitHub Actions abil.

### Tootmiskeskkond

- **Host:** Zone.ee jagatud hosting (`fototeek.ta24aksalu.itmajakas.ee`)
- **Veebiserver:** nginx + PHP-FPM 8.3
- **Andmebaas:** SQLite (jagatud kaustas)
- **Pildid:** `storage/app/public/memories/`, sümlingitud kausta `public/storage`

### CI / CD töövood

`.github/workflows/` sisaldab kahte töövoogu:

| Töövoog | Käivitub | Mida teeb |
|---------|----------|-----------|
| **CI** | iga push'iga | Laravel Pint koodistiili kontroll, automaattestid |
| **Production deploy** | manuaalselt (`workflow_dispatch`) | Deployer juurutab uue versiooni serverisse |

### Jagatud kaustad (oluline!)

`deploy.php` failis on määratud `shared_dirs`, mis tagavad andmete püsivuse üle juurutuste:

```php
set('shared_dirs', [
    'laravel-backend/storage',     // Logifailid, sessioonid, üleslaaditud pildid
    'laravel-backend/database',    // SQLite andmebaasifail
]);
```

Esmane setup tehakse käsitsi serveris käsuga `scripts/setup-shared-sqlite.sh`, mis kopeerib andmebaasifaili jagatud kausta. Pärast seda kasutavad kõik release'd sama andmebaasi ning kasutajaandmed püsivad ka pärast iga uut deploy'd.

### Juurutamise käivitamine

1. Tee muudatused, commit + push `main`-harusse
2. Oota CI roheline ✓
3. GitHub → Actions → Production deploy → Run workflow → `main` → Run workflow
4. ~1-3 minuti pärast on uus versioon live: https://fototeek.ta24aksalu.itmajakas.ee

---

## Tehnilised märkused

### Pildid: multipart/form-data (mitte Base64)

Pildid saadetakse serverisse `multipart/form-data` päringuga, mitte JSON-i sees Base64-kodeerituna. Selle põhjuseks on Zone.ee jagatud hostingu nginx-tasemel JSON-päringute range suuruspiirang. Multipart-päringud kasutavad eraldi konfiguratsiooni (`client_max_body_size`), mis on oluliselt suurem.

Pildi töötluse käigus:
1. Frontend (`src/utils/imageResize.js`) tagastab `File`-objekti (mitte Base64-stringi)
2. Backend (`MemoryController`) võtab vastu `multipart/form-data` ja kasutab PHP GD-laiendust pildi resize-imiseks (max 1920px täispildile, 400px pisipildile)
3. Andmebaasi salvestatakse ainult failitee (`memories/memory_X_xxx_full.jpg`), mitte pildi sisu

### URL-ide resolveerimine

`MemoryController::resolveImageUrl()` tagastab täielikud URL-id, kasutades `config('app.url')` väärtust. Iga keskkonna jaoks tuleb `.env` failis õige `APP_URL` seadistada (lokaalselt `http://127.0.0.1:8000`, tootmises `https://fototeek.ta24aksalu.itmajakas.ee`).

### Andmebaasi jagatud kaust

Deployer-i `shared_dirs` lisati `laravel-backend/database`, mis tagab et SQLite andmebaasifail jääb püsima üle juurutuste. Ilma selleta lõi iga `php artisan migrate --force` deploy ajal uue tühja andmebaasi.

---

## Andmebaasi püsivus toodangus (ÜHEKORDNE SEADISTUS)

Enne esimest deploy'd peale selle muudatuse on vaja serveris (Zone.ee SSH) teha:

```bash
ssh virt137753@ta24aksalu.itmajakas.ee
cd ~/domeenid/www.ta24aksalu.itmajakas.ee/fototeek/shared/laravel-backend

# Kui praegu on andmebaasis väärtuslikke andmeid, kopeeri need välja ENNE deploy'd:
mkdir -p database
cp ../../current/laravel-backend/database/database.sqlite database/database.sqlite 2>/dev/null || touch database/database.sqlite

chmod 664 database/database.sqlite
chmod 775 database
```

Seejärel veendu, et `~/domeenid/www.ta24aksalu.itmajakas.ee/fototeek/shared/laravel-backend/.env` failis on:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/data01/virt137753/domeenid/www.ta24aksalu.itmajakas.ee/fototeek/shared/laravel-backend/database/database.sqlite
```

(Absoluutne tee on tähtis — Laravel ei oska muidu SQLite faili leida, kui `database/` on sümlink jagatud kausta.)

---

## Dokumentatsioon ja tööriistad

- **Confluence:** https://aksaluhendrik.atlassian.net/wiki/spaces/Fotoalbum/overview?homepageId=327792
- **Jira:** https://aksaluhendrik.atlassian.net/jira/software/projects/FOT/boards/7
- **Märkus:** README koostamisel oli abiks Cursor agent.

---

## Taustainfo (projekti mõte)

Rakendus on mõeldud **peredele ja suguvõsadele**, et digitaliseerida ja säilitada fotosid ning mälestuste tekste, jagada albumeid ning hallata oma kontot (sh profiil ja salasõna). Visuaal ja funktsionaalsus laienevad vastavalt lõputöö ulatusele.

---

## Litsents

Õppeotstarbeline projekt — Kuressaare Ametikool, noorem-tarkvaraarendaja TA-24 lõputöö.
