<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Memory;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemoryController extends Controller
{
    private function mergeCamelCaseMemoryFields(Request $request): void
    {
        $request->merge([
            'image_url' => $request->input('image_url', $request->input('imageUrl')),
            'image_thumb_url' => $request->input('image_thumb_url', $request->input('imageThumbUrl')),
            'photo_class' => $request->input('photo_class', $request->input('photoClass')),
            'face_markers' => $request->input('face_markers', $request->input('faceMarkers')),
        ]);
    }

    public function index(Request $request, Album $album): JsonResponse
    {
        if (! $album->userCanAccess($request->user())) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $memories = $album->memories()->orderBy('id')->get();

        return response()->json([
            'memories' => $memories->map(fn (Memory $m) => $this->formatMemory($m))->all(),
        ]);
    }

    public function store(Request $request, Album $album): JsonResponse
    {
        if (! $album->userCanEdit($request->user())) {
            return response()->json(['message' => 'Sul pole õigust sellesse albumisse pilte lisada.'], 403);
        }

        $this->mergeCamelCaseMemoryFields($request);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'story' => ['nullable', 'string'],
            'who' => ['nullable', 'string'],
            'when' => ['nullable', 'string', 'max:255'],
            'where' => ['nullable', 'string'],
            // Base64 data URLs can get large; keep under typical MySQL packet/proxy limits.
            'image_url' => ['nullable', 'string', 'max:800000'],
            'image_thumb_url' => ['nullable', 'string', 'max:160000'],
            'photo_class' => ['nullable', 'string', 'max:64'],
            'favorite' => ['nullable', 'boolean'],
            'rotate' => ['nullable', 'string', 'max:32'],
            'face_markers' => ['nullable', 'array'],
        ]);

        $memory = new Memory([
            'album_id' => $album->id,
            'user_id' => $request->user()->id,
            'title' => $validated['title'] ?? 'Uus pilt',
            'story' => $validated['story'] ?? '',
            'who' => $validated['who'] ?? '',
            'when' => $validated['when'] ?? '',
            'where_note' => $validated['where'] ?? '',
            'image_url' => $validated['image_url'] ?? '',
            'image_thumb_url' => $validated['image_thumb_url'] ?? '',
            'photo_class' => $validated['photo_class'] ?? 'one',
            'favorite' => $validated['favorite'] ?? false,
            'rotate' => $validated['rotate'] ?? '',
            'face_markers' => $validated['face_markers'] ?? [],
        ]);
        try {
            $memory->save();
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Pildi salvestamine ebaõnnestus serveri piirangute tõttu. Proovi väiksemat pilti.',
            ], 413);
        }

        if ($memory->image_thumb_url) {
            $album->syncCoverFromThumb($memory->image_thumb_url);
        }

        $album->loadCount('memories');

        return response()->json([
            'memory' => $this->formatMemory($memory),
            'album' => [
                'id' => $album->id,
                'memories' => $album->memories()->count(),
                'coverThumbUrl' => $album->fresh()->cover_thumb_url,
            ],
        ], 201);
    }

    public function update(Request $request, Memory $memory): JsonResponse
    {
        $album = $memory->album;
        if (! $album->userCanEdit($request->user())) {
            return response()->json(['message' => 'Sul pole õigust seda mälestust muuta.'], 403);
        }

        $this->mergeCamelCaseMemoryFields($request);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'story' => ['sometimes', 'nullable', 'string'],
            'who' => ['sometimes', 'nullable', 'string'],
            'when' => ['sometimes', 'nullable', 'string', 'max:255'],
            'where' => ['sometimes', 'nullable', 'string'],
            'image_url' => ['sometimes', 'nullable', 'string', 'max:800000'],
            'image_thumb_url' => ['sometimes', 'nullable', 'string', 'max:160000'],
            'photo_class' => ['sometimes', 'nullable', 'string', 'max:64'],
            'favorite' => ['sometimes', 'boolean'],
            'rotate' => ['sometimes', 'nullable', 'string', 'max:32'],
            'face_markers' => ['sometimes', 'nullable', 'array'],
        ]);

        $payload = [];
        foreach (['title', 'story', 'who', 'when', 'photo_class', 'favorite', 'rotate', 'image_url', 'image_thumb_url', 'face_markers'] as $field) {
            if (array_key_exists($field, $validated)) {
                $payload[$field] = $validated[$field];
            }
        }
        if (array_key_exists('where', $validated)) {
            $payload['where_note'] = $validated['where'];
        }

        $memory->fill($payload);
        try {
            $memory->save();
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Pildi salvestamine ebaõnnestus serveri piirangute tõttu. Proovi väiksemat pilti.',
            ], 413);
        }

        if ($memory->image_thumb_url) {
            $album->syncCoverFromThumb($memory->image_thumb_url);
        }

        return response()->json([
            'memory' => $this->formatMemory($memory->fresh()),
        ]);
    }

    public function destroy(Request $request, Memory $memory): JsonResponse
    {
        $album = $memory->album;
        if (! $album->userCanEdit($request->user())) {
            return response()->json(['message' => 'Sul pole õigust seda mälestust kustutada.'], 403);
        }

        $memory->delete();

        $nextThumb = $album->memories()->orderByDesc('updated_at')->value('image_thumb_url');
        $album->cover_thumb_url = $nextThumb;
        $album->saveQuietly();

        return response()->json([
            'message' => 'Mälestus kustutatud.',
            'album' => [
                'id' => $album->id,
                'memories' => $album->memories()->count(),
                'coverThumbUrl' => $album->fresh()->cover_thumb_url,
            ],
        ]);
    }

    private function formatMemory(Memory $memory): array
    {
        return [
            'id' => $memory->id,
            'title' => $memory->title,
            'story' => $memory->story ?? '',
            'who' => $memory->who ?? '',
            'when' => $memory->when ?? '',
            'where' => $memory->where_note ?? '',
            'imageUrl' => $memory->image_url ?? '',
            'imageThumbUrl' => $memory->image_thumb_url ?? '',
            'photoClass' => $memory->photo_class,
            'favorite' => (bool) $memory->favorite,
            'rotate' => $memory->rotate ?? '',
            'faceMarkers' => $memory->face_markers ?? [],
        ];
    }
}
