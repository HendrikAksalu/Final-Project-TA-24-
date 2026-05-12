<?php

namespace App\Console\Commands;

use App\Models\Memory;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Loob vanade mälestuste pisipildid uuesti suurema (800 px) ja kvaliteetsemana,
 * et albumi vaates teravus retina-ekraanidel paraneks.
 *
 *   php artisan memories:regenerate-thumbs            # tegelikult genereerib
 *   php artisan memories:regenerate-thumbs --dry-run  # ainult loendab, ei muuda
 *   php artisan memories:regenerate-thumbs --force    # genereerib ka olemasolevad uuesti
 */
class RegenerateMemoryThumbnails extends Command
{
    protected $signature = 'memories:regenerate-thumbs {--dry-run : Ainult loenda, ära muuda} {--force : Genereeri ka olemasolevad pisipildid uuesti}';

    protected $description = 'Genereerib mälestuste pisipildid uuesti (800 px, JPEG 82) täisversiooni põhjal.';

    private const MAX_DIM = 800;

    private const QUALITY = 82;

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        if (! function_exists('imagecreatefromjpeg')) {
            $this->error('PHP GD pole laaditud — pisipilte ei saa genereerida.');

            return self::FAILURE;
        }

        $processed = 0;
        $skipped = 0;
        $failed = 0;

        Memory::query()
            ->whereNotNull('image_url')
            ->where('image_url', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($memories) use (&$processed, &$skipped, &$failed, $dryRun, $force): void {
                foreach ($memories as $memory) {
                    $fullRel = $this->relativePath($memory->image_url);
                    if (! $fullRel) {
                        $skipped++;
                        continue;
                    }

                    $fullAbs = storage_path('app/public/'.$fullRel);
                    if (! is_file($fullAbs)) {
                        $this->warn("Puudub: $fullAbs");
                        $skipped++;
                        continue;
                    }

                    $thumbRel = $this->relativePath($memory->image_thumb_url) ?: 'memories/thumbs/'.basename($fullRel, '_full.jpg').'_thumb.jpg';
                    $thumbAbs = storage_path('app/public/'.$thumbRel);

                    if (! $force && is_file($thumbAbs)) {
                        // Kontrolli kas vana pisipilt on juba uue suurusega.
                        $info = @getimagesize($thumbAbs);
                        if ($info && max($info[0], $info[1]) >= self::MAX_DIM - 16) {
                            $skipped++;
                            continue;
                        }
                    }

                    if ($dryRun) {
                        $this->line("Genereeriks: $thumbRel");
                        $processed++;
                        continue;
                    }

                    if (! $this->regenerate($fullAbs, $thumbAbs)) {
                        $this->warn("Ebaõnnestus: $fullAbs");
                        $failed++;
                        continue;
                    }

                    if (! $memory->image_thumb_url || ! Str::contains($memory->image_thumb_url, $thumbRel)) {
                        $memory->image_thumb_url = $thumbRel;
                        $memory->saveQuietly();
                    }
                    $processed++;
                }
            });

        $this->info("Genereeritud: $processed, vahele jäetud: $skipped, vigased: $failed");

        return self::SUCCESS;
    }

    private function relativePath(?string $url): string
    {
        if (! $url) {
            return '';
        }
        if (Str::startsWith($url, ['data:', 'http://', 'https://'])) {
            $parts = parse_url($url);
            $path = $parts['path'] ?? '';
            $path = Str::after($path, '/storage/');

            return ltrim($path, '/');
        }
        if (Str::startsWith($url, '/storage/')) {
            return ltrim(Str::after($url, '/storage/'), '/');
        }

        return ltrim($url, '/');
    }

    private function regenerate(string $fullAbs, string $thumbAbs): bool
    {
        $mime = mime_content_type($fullAbs);
        $img = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($fullAbs),
            'image/png' => @imagecreatefrompng($fullAbs),
            'image/webp' => @imagecreatefromwebp($fullAbs),
            'image/gif' => @imagecreatefromgif($fullAbs),
            default => false,
        };
        if (! $img) {
            return false;
        }

        $w = imagesx($img);
        $h = imagesy($img);
        $ratio = min(self::MAX_DIM / $w, self::MAX_DIM / $h, 1);
        $newW = max(1, (int) round($w * $ratio));
        $newH = max(1, (int) round($h * $ratio));

        $resized = $ratio < 1 ? imagecreatetruecolor($newW, $newH) : $img;
        if ($ratio < 1) {
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $w, $h);
        }

        @mkdir(dirname($thumbAbs), 0775, true);
        $ok = imagejpeg($resized, $thumbAbs, self::QUALITY);

        if ($resized !== $img) {
            imagedestroy($resized);
        }
        imagedestroy($img);

        return (bool) $ok;
    }
}
