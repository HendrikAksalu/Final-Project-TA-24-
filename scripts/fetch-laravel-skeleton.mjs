import { mkdir, writeFile } from 'node:fs/promises'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'

const root = join(dirname(fileURLToPath(import.meta.url)), '..')
const base = 'https://raw.githubusercontent.com/laravel/laravel/11.x'

const files = [
  '.editorconfig',
  '.env.example',
  '.gitattributes',
  'artisan',
  'bootstrap/app.php',
  'bootstrap/providers.php',
  'composer.json',
  'phpunit.xml',
  'public/index.php',
  'public/.htaccess',
  'routes/console.php',
  'routes/web.php',
  'app/Http/Controllers/Controller.php',
  'app/Models/User.php',
  'app/Providers/AppServiceProvider.php',
  'config/app.php',
  'config/auth.php',
  'config/cache.php',
  'config/database.php',
  'config/filesystems.php',
  'config/logging.php',
  'config/mail.php',
  'config/queue.php',
  'config/services.php',
  'config/session.php',
  'database/.gitignore',
  'database/factories/UserFactory.php',
  'database/seeders/DatabaseSeeder.php',
  'database/migrations/0001_01_01_000000_create_users_table.php',
  'database/migrations/0001_01_01_000001_create_cache_table.php',
  'database/migrations/0001_01_01_000002_create_jobs_table.php',
  'resources/css/app.css',
  'resources/js/app.js',
  'resources/views/welcome.blade.php',
  'storage/app/.gitignore',
  'storage/app/private/.gitignore',
  'storage/app/public/.gitignore',
  'storage/framework/.gitignore',
  'storage/framework/cache/.gitignore',
  'storage/framework/cache/data/.gitignore',
  'storage/framework/sessions/.gitignore',
  'storage/framework/testing/.gitignore',
  'storage/framework/views/.gitignore',
  'storage/logs/.gitignore',
  'tests/TestCase.php',
  'tests/Feature/ExampleTest.php',
  'tests/Unit/ExampleTest.php',
]

async function main() {
  for (const rel of files) {
    const url = `${base}/${rel}`
    const res = await fetch(url)
    if (!res.ok) throw new Error(`${url} -> ${res.status}`)
    const dest = join(root, rel)
    await mkdir(dirname(dest), { recursive: true })
    await writeFile(dest, await res.text())
    console.log('wrote', rel)
  }
}

main().catch((e) => {
  console.error(e)
  process.exit(1)
})
