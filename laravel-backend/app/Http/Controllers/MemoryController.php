<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Memory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

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
            'image' => ['nullable', 'file', 'image', 'max:20480'],
            'photo_class' => ['nullable', 'string', 'max:64'],
            'favorite' => ['nullable', 'boolean'],
            'rotate' => ['nullable', 'string', 'max:32'],
            'face_markers' => ['nullable'],
        ]);

        $imagePath = null;
        $thumbPath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->storeImage($request->file('image'), (int) $album->id);
            $thumbPath = $this->createThumbnail($request->file('image'), (int) $album->id);
        }

        $faceMarkers = $validated['face_markers'] ?? [];
        if (is_string($faceMarkers)) {
            $faceMarkers = json_decode($faceMarkers, true) ?? [];
        }
        if (! is_array($faceMarkers)) {
            $faceMarkers = [];
        }

        $memory = Memory::create([
            'album_id' => $album->id,
            'user_id' => $request->user()->id,
            'title' => $validated['title'] ?? 'Uus pilt',
            'story' => $validated['story'] ?? '',
            'who' => $validated['who'] ?? '',
            'when' => $validated['when'] ?? '',
            'where_note' => $validated['where'] ?? '',
            'image_url' => $imagePath ?? '',
            'image_thumb_url' => $thumbPath ?? '',
            'photo_class' => $validated['photo_class'] ?? 'one',
            'favorite' => filter_var($validated['favorite'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'rotate' => $validated['rotate'] ?? '',
            'face_markers' => $faceMarkers,
        ]);

        if ($thumbPath) {
            $album->syncCoverFromThumb($thumbPath);
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
            'image' => ['sometimes', 'file', 'image', 'max:20480'],
            'image_url' => ['sometimes', 'nullable', 'string'],
            'image_thumb_url' => ['sometimes', 'nullable', 'string'],
            'photo_class' => ['sometimes', 'nullable', 'string', 'max:64'],
            'favorite' => ['sometimes', 'boolean'],
            'rotate' => ['sometimes', 'nullable', 'string', 'max:32'],
            'face_markers' => ['sometimes', 'nullable'],
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
        if (array_key_exists('face_markers', $validated)) {
            $decodedMarkers = $validated['face_markers'];
            if (is_string($decodedMarkers)) {
                $decodedMarkers = json_decode($decodedMarkers, true) ?? [];
            }
            $payload['face_markers'] = is_array($decodedMarkers) ? $decodedMarkers : [];
        }
        if ($request->hasFile('image')) {
            $payload['image_url'] = $this->storeImage($request->file('image'), (int) $album->id);
            $payload['image_thumb_url'] = $this->createThumbnail($request->file('image'), (int) $album->id);
        }

        $memory->fill($payload);
        $memory->save();

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
            'imageUrl' => $this->resolveImageUrl($memory->image_url),
            'imageThumbUrl' => $this->resolveImageUrl($memory->image_thumb_url),
            'photoClass' => $memory->photo_class,
            'favorite' => (bool) $memory->favorite,
            'rotate' => $memory->rotate ?? '',
            'faceMarkers' => $memory->face_markers ?? [],
        ];
    }

    private function resolveImageUrl(?string $path): string
    {
        if (! $path) return '';
        if (str_starts_with($path, 'data:') || str_starts_with($path, 'http') || str_starts_with($path, '/')) {
            return $path;
        }
        return '/storage/'.ltrim($path, '/');
    }

    private function storeImage(UploadedFile $file, int $albumId): string
    {
        $filename = 'memory_'.$albumId.'_'.uniqid('', true).'_full.jpg';
        $path = 'memories/'.$filename;
        $img = $this->loadImageFromUpload($file);
        if (! $img) {
            return $file->storeAs('memories', $filename, 'public');
        }

        $resized = $this->resizeImage($img, 1920);
        $fullPath = storage_path('app/public/'.$path);
        @mkdir(dirname($fullPath), 0775, true);
        imagejpeg($resized, $fullPath, 88);
        if ($resized !== $img) {
            imagedestroy($img);
        }
        imagedestroy($resized);
        return $path;
    }

    private function createThumbnail(UploadedFile $file, int $albumId): string
    {
        $filename = 'memory_'.$albumId.'_'.uniqid('', true).'_thumb.jpg';
        $path = 'memories/thumbs/'.$filename;
        $img = $this->loadImageFromUpload($file);
        if (! $img) return '';

        $resized = $this->resizeImage($img, 400);
        $fullPath = storage_path('app/public/'.$path);
        @mkdir(dirname($fullPath), 0775, true);
        imagejpeg($resized, $fullPath, 75);
        if ($resized !== $img) {
            imagedestroy($img);
        }
        imagedestroy($resized);
        return $path;
    }

    private function loadImageFromUpload(UploadedFile $file)
    {
        $mime = $file->getMimeType();
        $tmpPath = $file->getRealPath();
        return match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($tmpPath),
            'image/png' => @imagecreatefrompng($tmpPath),
            'image/webp' => @imagecreatefromwebp($tmpPath),
            'image/gif' => @imagecreatefromgif($tmpPath),
            default => false,
        };
    }

    private function resizeImage($img, int $maxDim)
    {
        $w = imagesx($img);
        $h = imagesy($img);
        if ($w <= $maxDim && $h <= $maxDim) return $img;
        $ratio = min($maxDim / $w, $maxDim / $h);
        $newW = (int) round($w * $ratio);
        $newH = (int) round($h * $ratio);
        $new = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($new, $img, 0, 0, 0, 0, $newW, $newH, $w, $h);
        return $new;
    }
}
