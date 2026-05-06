<?php

/**
 * Fototeek — täisladu kogu repost (Vue lähtub ../src, Laravel on laravel-backend/).
 *
 * Enne esimest käiku loo serveris ühisfail:
 *   ~/domeenid/www.ta24aksalu.itmajakas.ee/fototeek/shared/laravel-backend/.env
 *
 * Zone veebiserveri dokumentjuur peaks olema:
 *   .../fototeek/current/laravel-backend/public
 *
 * Kohalik käsk (SSH võti ja git ligipääs peaksid töötama):
 *   dep deploy stage -vvv
 */

namespace Deployer;

require 'recipe/common.php';

set('repository', 'git@github.com:HendrikAksalu/Final-Project-TA-24-.git');
set('keep_releases', 2);

set('shared_dirs', [
    'laravel-backend/storage',
]);

set('shared_files', [
    'laravel-backend/.env',
]);

set('writable_dirs', [
    'laravel-backend/bootstrap/cache',
    'laravel-backend/storage',
]);

host('stage')
    ->setHostname('ta24aksalu.itmajakas.ee')
    ->setRemoteUser('virt137753')
    ->setDeployPath('~/domeenid/www.ta24aksalu.itmajakas.ee/fototeek')
    ->setIdentityFile('~/.ssh/id_ed25519')
    ->set('labels', ['stage'])
    ->set('env', [
        'GIT_SSH_COMMAND' => 'ssh -i ~/.ssh/id_ed25519 -o StrictHostKeyChecking=accept-new',
    ]);

desc('Install Composer deps (Laravel)');
task('deploy:vendors', function () {
    run('cd {{release_path}}/laravel-backend && {{bin/composer}} {{composer_action}} {{composer_options}} 2>&1');
});

desc('Install NPM deps & Vite build');
task('npm:production', function () {
    run('cd {{release_path}}/laravel-backend && (npm ci --no-audit --no-fund || npm install --no-audit --no-fund)');
    run('cd {{release_path}}/laravel-backend && npm run build');
});

desc('Run migrations');
task('artisan:migrate', function () {
    run('cd {{release_path}}/laravel-backend && {{bin/php}} artisan migrate --force');
});

desc('Storage symlink');
task('artisan:storage:link', function () {
    run('cd {{release_path}}/laravel-backend && {{bin/php}} artisan storage:link');
});

desc('Clear caches');
task('artisan:optimize:clear', function () {
    run('cd {{release_path}}/laravel-backend && {{bin/php}} artisan optimize:clear');
});

desc('Optimize');
task('artisan:optimize', function () {
    run('cd {{release_path}}/laravel-backend && {{bin/php}} artisan optimize');
});

desc('Clear Zone PHP CGI opcode cache (best effort)');
task('deploy:zon-php-clear', function () {
    run('killall php84-cgi 2>/dev/null || killall php83-cgi 2>/dev/null || killall php-cgi 2>/dev/null || true');
});

task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'npm:production',
    'artisan:migrate',
    'artisan:storage:link',
    'artisan:optimize:clear',
    'artisan:optimize',
    'deploy:publish',
]);

after('deploy:success', 'deploy:zon-php-clear');
after('deploy:failed', 'deploy:unlock');
