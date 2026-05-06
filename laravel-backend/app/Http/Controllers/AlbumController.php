<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlbumController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $owned = Album::query()
            ->where('user_id', $user->id)
            ->withCount('memories')
            ->orderBy('created_at')
            ->get();

        $shared = Album::query()
            ->whereHas('collaborators', fn ($q) => $q->where('users.id', $user->id))
            ->withCount('memories')
            ->orderBy('created_at')
            ->get();

        $items = $owned->concat($shared)->unique('id')->sortBy('created_at')->values();

        return response()->json([
            'albums' => $items->map(fn (Album $album) => $this->formatAlbum($album, $user))->all(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->merge([
            'photo_class' => $request->input('photo_class', $request->input('photoClass')),
        ]);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'photo_class' => ['nullable', 'string', 'max:64'],
            'rotate' => ['nullable', 'string', 'max:32'],
        ]);

        $album = Album::query()->create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'] ?? 'Uus album',
            'photo_class' => $validated['photo_class'] ?? 'beach',
            'rotate' => $validated['rotate'] ?? '',
        ]);

        $album->loadCount('memories');

        return response()->json([
            'album' => $this->formatAlbum($album, $request->user()),
        ], 201);
    }

    public function show(Request $request, Album $album): JsonResponse
    {
        if (! $album->userCanAccess($request->user())) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $album->loadCount('memories');

        return response()->json([
            'album' => $this->formatAlbum($album, $request->user()),
        ]);
    }

    public function update(Request $request, Album $album): JsonResponse
    {
        if ($album->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Ainult albumi omanik saab seda muuta.'], 403);
        }

        $request->merge([
            'photo_class' => $request->input('photo_class', $request->input('photoClass')),
        ]);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'photo_class' => ['sometimes', 'string', 'max:64'],
            'rotate' => ['sometimes', 'string', 'max:32'],
        ]);

        $album->fill($validated);
        $album->save();
        $album->loadCount('memories');

        return response()->json([
            'album' => $this->formatAlbum($album, $request->user()),
        ]);
    }

    public function destroy(Request $request, Album $album): JsonResponse
    {
        if ($album->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Ainult albumi omanik saab selle kustutada.'], 403);
        }

        $album->delete();

        return response()->json(['message' => 'Album kustutatud.']);
    }

    private function formatAlbum(Album $album, User $viewer): array
    {
        return [
            'id' => $album->id,
            'title' => $album->title,
            'memories' => (int) ($album->memories_count ?? $album->memories()->count()),
            'photoClass' => $album->photo_class,
            'rotate' => $album->rotate,
            'coverThumbUrl' => $album->cover_thumb_url,
            'myRole' => $album->userRole($viewer),
            'isSharedWithMe' => $album->user_id !== $viewer->id,
        ];
    }
}
