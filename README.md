# Fototeek — fotoalbum peredele mälestuste kogumiseks

Veebipõhine rakendus **pere mälestuste ja fotode kogumiseks**: albumid, mälestuste kaardid, piltide lisamine, jagamine kaastöötajatega ning kasutaja profiili ja salasõna haldus.

**Frontend** on Vue 3 üheleherakendus (SPA), mis suhtleb **Laravel REST API** kaudu; autentimine toimib **Laravel Sanctumi** isikliku juurdepääsuloa tokeniga (`Bearer`), mitte Inertia sessiooniga.

---

## Monorepo: miks backend ja frontend on ühes repositooriumis

Projekt on korraldatud **monorepo** põhimõttel: Laravel asub kaustas `laravel-backend/`, Vue SPA repo **juurkaustas** (`src/`, juurekausta `vite.config.js`). See erineb Aiapäeviku-tüüpi ühest Laravel+Inertia tervikust, kuid monorepo põhjendused on sarnased.

### Miks see valik sobib sellele rakendusele

- **Üks checkout** — õppeprojekti ja väikese tiimi jaoks piisab ühest `git clone` käsust; API ja UI muutuvad koos ühes PR-is (nt uus väli: migratsioon + kontroller + vorm).
- **Versioonid sobivad kokku** — sama commit kirjeldab nii API kui SPA käitumist; väheneb „kas backend või frontend on vale release’i“ segadus.
- **Juurutus** — toodangus võid teenindada Laravelit ja staatilist SPA buildi ühest paigalduspunktist (nt Laravel `public` kaudu); üks repo hoiab dokumentatsiooni ja skripte koos.

### Plussid ja miinused

| Pluss | Lühikirjeldus |
|--------|----------------|
| Aatomilised muudatused | Üks PR võib hõlmata migratsiooni, API ja Vue muudatust. |
| Ühtne versioon | `main` / tag kirjeldab kogu rakenduse seisu. |
| Lihtsam sisseelamine | Selge jaotus: `laravel-backend/` = API, juur = SPA. |

| Miinus | Praktiline tähendus |
|--------|---------------------|
| Kaks Node ja PHP töövoogu | Arendaja käivitab nii `composer`/`artisan` kui juurekausta `npm run dev`. |
| Mahukas lokaalselt | `vendor/` ja `node_modules/` (juur + võimalik `laravel-backend` oma npm). |
| CORS ja API baas-URL | SPA jaoks vaja `VITE_API_BASE_URL` ja Laravelis `FRONTEND_URL` (vt allpool). |

---

## Tehnoloogiad

| Kiht | Tehnoloogia |
|------|--------------|
| Backend | PHP ^8.3 · Laravel ^13 · Laravel Sanctum |
| Frontend (SPA) | Vue ^3.5 · Vue Router ^4 · Vite ^7 (repo juur) |
| API | JSON REST (`/api/...`) · token autentimine |
| Stiil | Juurekausta SPA kasutab projektiga kaasas olevaid CSS-lahendusi; `laravel-backend` sisaldab eraldi Vite/Tailwind seadistust Laraveli tarbeks |
| Testid (backend) | PHPUnit (`php artisan test`) · Laravel Pint |

---

## Eeltingimused

- **PHP 8.3+** ja **Composer 2+**
- **Node.js** (soovituslik **20.19+**, vt `package.json` `engines`)
- **SQLite** (vaikimisi Laravel `.env.example`) või **MySQL**

---

## Kiirkäivitus (soovituslik arendus)

Kaks protsessi: API server ja Vue arendusserver.

### 1) Laravel API (`laravel-backend`)

```bash
cd laravel-backend
cp .env.example .env
php artisan key:generate
```

**SQLite** (kui `.env` näitab `DB_CONNECTION=sqlite`):

```bash
touch database/database.sqlite
php artisan migrate
```

**MySQL**: täida `.env` failis `DB_*` väljad, seejärel `php artisan migrate`.

Oluline SPA jaoks:

```env
APP_URL=http://127.0.0.1:8000
FRONTEND_URL=http://127.0.0.1:5173
```

Käivita API:

```bash
php artisan serve
```

### 2) Vue SPA (repo juur)

```bash
cd ..   # repo juur
cp .env.example .env
# Vaikimisi: VITE_API_BASE_URL=http://127.0.0.1:8000
npm install
npm run dev
```

Ava brauseris **`http://127.0.0.1:5173`** (või Vite väljastatud port). API päringud lähevad `VITE_API_BASE_URL` alla.

> **Märkus:** `laravel-backend` sisaldab ka `composer run setup` / `composer run dev` skripte (Laraveli oma Vite jms). Juurekausta SPA arendamiseks piisab tavaliselt ülaltoodud kaheastmelisest käivitusest.

---

## Keskkonnamuutujad (lühike kokkuvõte)

| Fail | Mõte |
|------|------|
| `laravel-backend/.env` | `APP_URL`, `FRONTEND_URL`, `DB_*`, `APP_KEY` |
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
./vendor/bin/pint          # koodistiil (kui paigaldatud)
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

Täpse Zone/GitHub Actions töövoo kirjeldus sõltub sinu hostist. Üldiselt:

1. Laravel: `composer install --no-dev`, `php artisan migrate --force`, `.env` toodangu väärtused.
2. SPA: `npm run build` **repo juures**, tõsta väljund (`dist/`) sinna, kuidas Laravel või CDN staatikut teenib (nt `public/` või eraldi staatiline host).

---

## Dokumentatsioon ja tööriistad

- **Confluence:** https://aksaluhendrik.atlassian.net/wiki/spaces/Fotoalbum/overview?homepageId=327792  
- **Jira:** https://aksaluhendrik.atlassian.net/jira/software/projects/FOT/boards/7  
- **Märkus:** README koostamisel oli abiks Cursor agent.

---

## Taustainfo (projekti mõte)

Rakendus on mõeldud **peredele ja suguvõsadele**, et digitaliseerida ja säilitada fotosid ning mälestuste tekste, jagada albumeid ning hallata oma kontot (sh profiil ja salasõna). Visuaal ja funktsionaalsus laienevad vastavalt lõputöö ulatusele.
