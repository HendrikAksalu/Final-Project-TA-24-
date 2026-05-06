<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AlbumPatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_patch_only_title_does_not_require_photo_class(): void
    {
        $user = User::factory()->create();
        $album = Album::query()->create([
            'user_id' => $user->id,
            'title' => 'Uus album 1',
            'photo_class' => 'beach',
            'rotate' => '',
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/albums/{$album->id}", [
            'title' => 'Renamed album',
        ]);

        $response->assertOk();
        $response->assertJsonPath('album.title', 'Renamed album');
        $this->assertSame('beach', $album->fresh()->photo_class);
    }
}
