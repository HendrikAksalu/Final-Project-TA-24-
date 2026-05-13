<?php

namespace App\Console\Commands;

use App\Models\Album;
use Illuminate\Console\Command;

/**
 * Ühekordne / hooldus: joondab albums.cover_thumb_url esimese loetletud mälestuse pisipildiga.
 *
 *   php artisan albums:sync-covers
 *   php artisan albums:sync-covers --dry-run
 */
class SyncAlbumCovers extends Command
{
    protected $signature = 'albums:sync-covers {--dry-run : Näita, millised albumid muutuksid, ära salvesta}';

    protected $description = 'Uuendab albumite kaanepildid vastavalt esimesele mälestusele (id kasvavalt), millel pisipilt olemas.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $wouldChange = 0;
        $alreadyOk = 0;

        Album::query()->orderBy('id')->chunkById(100, function ($albums) use ($dryRun, &$wouldChange, &$alreadyOk): void {
            foreach ($albums as $album) {
                $expected = $album->memories()
                    ->where('image_thumb_url', '!=', '')
                    ->orderBy('id')
                    ->value('image_thumb_url');

                $expected = $expected ?: null;
                $current = $album->cover_thumb_url ?: null;

                if ($current === $expected) {
                    $alreadyOk++;

                    continue;
                }

                $wouldChange++;
                if ($dryRun) {
                    $this->line("Album #{$album->id} «{$album->title}»: kaas vajaks uuendust.");
                } else {
                    $album->syncCoverToFirstListedThumbnail();
                    $this->line("Album #{$album->id} «{$album->title}»: kaas uuendatud.");
                }
            }
        });

        $verb = $dryRun ? 'vajaks uuendust' : 'uuendatud';
        $this->newLine();
        $this->info("Kokku: {$wouldChange} albumit {$verb}, {$alreadyOk} oli juba õige.");

        return self::SUCCESS;
    }
}
