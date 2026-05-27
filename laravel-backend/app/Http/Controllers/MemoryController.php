<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Memory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

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

    /**
     * PATCH-is ei tohi lisada image_* võtmeid kui klient neid ei saatnud — muidu Laravel muudab tühjad stringid nulliks
     * ja valideerija käsitleks välja „saadetuna“, mis võiks juhuslikult pilte kustutada.
     */
    private function mergePresentCamelCaseMemoryFields(Request $request): void
    {
        $payload = $request->isJson()
            ? ($request->json()?->all() ?? [])
            : $request->request->all();

        $merge = [];

        if (Arr::has($payload, 'imageUrl') || Arr::has($payload, 'image_url')) {
            $merge['image_url'] = $request->input('image_url', $request->input('imageUrl'));
        }

        if (Arr::has($payload, 'imageThumbUrl') || Arr::has($payload, 'image_thumb_url')) {
            $merge['image_thumb_url'] = $request->input('image_thumb_url', $request->input('imageThumbUrl'));
        }

        if (Arr::has($payload, 'photoClass') || Arr::has($payload, 'photo_class')) {
            $merge['photo_class'] = $request->input('photo_class', $request->input('photoClass'));
        }

        if (Arr::has($payload, 'faceMarkers') || Arr::has($payload, 'face_markers')) {
            $merge['face_markers'] = $request->input('face_markers', $request->input('faceMarkers'));
        }

        if ($merge !== []) {
            $request->merge($merge);
        }
    }

    public function index(Request $request, Album $album): JsonResponse
    {
        if (! $album->userCanAccess($request->user())) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $memories = $album->memories()->with('author:id,name')->orderBy('id')->get();

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
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:20480'],
            'photo_class' => ['nullable', 'string', 'max:64'],
            'favorite' => ['nullable', 'boolean'],
            'rotate' => ['nullable', 'string', 'max:32'],
            'face_markers' => ['nullable'],
        ]);

        $imagePath = null;
        $thumbPath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imagePath = $this->storeImage($file, (int) $album->id);
            $thumbPath = $this->createThumbnail($file, (int) $album->id);
            if ($imagePath === null || $thumbPath === '') {
                return response()->json(['message' => 'Üles laaditud fail ei ole kehtiv pilt.'], 422);
            }
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

        $album->syncCoverToFirstListedThumbnail();

        $album->loadCount('memories');

        return response()->json([
            'memory' => $this->formatMemory($memory),
            'album' => [
                'id' => $album->id,
                'memories' => $album->memories()->count(),
                'coverThumbUrl' => $this->resolveImageUrl($album->fresh()->cover_thumb_url),
            ],
        ], 201);
    }

    public function update(Request $request, Memory $memory): JsonResponse
    {
        $album = $memory->album;
        if (! $album->userCanEdit($request->user())) {
            return response()->json(['message' => 'Sul pole õigust seda mälestust muuta.'], 403);
        }

        $this->mergePresentCamelCaseMemoryFields($request);

        $validated = $request->validate([
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'story' => ['sometimes', 'nullable', 'string'],
            'who' => ['sometimes', 'nullable', 'string'],
            'when' => ['sometimes', 'nullable', 'string', 'max:255'],
            'where' => ['sometimes', 'nullable', 'string'],
            'image' => ['sometimes', 'nullable', 'file', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:20480'],
            'photo_class' => ['sometimes', 'nullable', 'string', 'max:64'],
            'favorite' => ['sometimes', 'nullable', 'boolean'],
            'rotate' => ['sometimes', 'nullable', 'string', 'max:32'],
            'face_markers' => ['sometimes', 'nullable'],
            'image_url' => ['sometimes', 'nullable', 'string'],
            'image_thumb_url' => ['sometimes', 'nullable', 'string'],
        ]);

        // Uuenda ainult tekstilisi välju, mis päringus olemas on (mitte file)
        $textFields = ['title', 'story', 'who', 'when', 'photo_class', 'rotate'];
        foreach ($textFields as $field) {
            if ($request->has($field)) {
                $value = $validated[$field] ?? '';
                $memory->{$field} = $value ?? '';
            }
        }

        // 'where' on andmebaasis 'where_note'
        if ($request->has('where')) {
            $memory->where_note = $validated['where'] ?? '';
        }

        // favorite on bool
        if ($request->has('favorite')) {
            $memory->favorite = filter_var($validated['favorite'] ?? false, FILTER_VALIDATE_BOOLEAN);
        }

        // face_markers võib tulla JSON stringina
        if ($request->has('face_markers')) {
            $faceMarkers = $validated['face_markers'] ?? [];
            if (is_string($faceMarkers)) {
                $faceMarkers = json_decode($faceMarkers, true) ?? [];
            }
            $memory->face_markers = $faceMarkers;
        }

        $clearedImagesViaJson = false;

        // JSON PATCH: tühjad image_url / image_thumb_url kustutavad failid („Eemalda pilt“).
        // ConvertEmptyStringsToNull middleware teeb tühjad stringid nulliks — peame aktsepteerima mõlemat.
        if (! $request->hasFile('image')) {
            $isEmptyImagePayload = static fn ($v) => $v === '' || $v === null;
            $wantsClearImage = array_key_exists('image_url', $validated) && $isEmptyImagePayload($validated['image_url']);
            $wantsClearThumb = array_key_exists('image_thumb_url', $validated) && $isEmptyImagePayload($validated['image_thumb_url']);
            if ($wantsClearImage || $wantsClearThumb) {
                $this->deleteStoredMemoryImageFiles($memory);
                $memory->image_url = '';
                $memory->image_thumb_url = '';
                $clearedImagesViaJson = true;
            }
        }

        // Kui on uus pildi fail
        if ($request->hasFile('image')) {
            // Kustuta vanad failid kui eksisteerivad
            if ($memory->image_url && ! str_starts_with($memory->image_url, 'data:') && ! str_starts_with($memory->image_url, 'http')) {
                @unlink(storage_path('app/public/'.$memory->image_url));
            }
            if ($memory->image_thumb_url && ! str_starts_with($memory->image_thumb_url, 'data:') && ! str_starts_with($memory->image_thumb_url, 'http')) {
                @unlink(storage_path('app/public/'.$memory->image_thumb_url));
            }

            $file = $request->file('image');
            $imagePath = $this->storeImage($file, $album->id);
            $thumbPath = $this->createThumbnail($file, $album->id);
            if ($imagePath === null || $thumbPath === '') {
                return response()->json(['message' => 'Üles laaditud fail ei ole kehtiv pilt.'], 422);
            }
            $memory->image_url = $imagePath;
            $memory->image_thumb_url = $thumbPath;
        }

        $memory->save();

        $album->syncCoverToFirstListedThumbnail();

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

        $album->syncCoverToFirstListedThumbnail();

        return response()->json([
            'message' => 'Mälestus kustutatud.',
            'album' => [
                'id' => $album->id,
                'memories' => $album->memories()->count(),
                'coverThumbUrl' => $this->resolveImageUrl($album->fresh()->cover_thumb_url),
            ],
        ]);
    }

    private function deleteStoredMemoryImageFiles(Memory $memory): void
    {
        foreach (['image_url', 'image_thumb_url'] as $field) {
            $path = $memory->{$field} ?? '';
            if ($path && ! str_starts_with($path, 'data:') && ! str_starts_with($path, 'http')) {
                @unlink(storage_path('app/public/'.$path));
            }
        }
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
            'authorName' => $memory->author?->name ?? 'Tundmatu',
            'createdAt' => optional($memory->created_at)?->toISOString(),
        ];
    }

    private function resolveImageUrl(?string $path): string
    {
        if (! $path) {
            return '';
        }
        // Vanad base64 ja täielikud URL-id jäävad alles
        if (str_starts_with($path, 'data:') || str_starts_with($path, 'http')) {
            return $path;
        }
        // Suhteline tee /storage/... muudame täielikuks URL-iks
        if (str_starts_with($path, '/')) {
            return rtrim(config('app.url'), '/').$path;
        }

        // Failitee storage'is — konstrueerime täieliku URL-i
        return rtrim(config('app.url'), '/').'/storage/'.ltrim($path, '/');
    }

    private function storeImage(UploadedFile $file, int $albumId): ?string
    {
        $filename = 'memory_'.$albumId.'_'.uniqid('', true).'_full.jpg';
        $path = 'memories/'.$filename;
        $img = $this->loadImageFromUpload($file);
        if (! $img) {
            return null;
        }

        // Kui klient saatis juba sobiva mõõduga JPEG-i, salvestame originaali ilma uue kompressioonita,
        // et vältida topelt-JPEG kvaliteedikadu (klient juba kompressis pildi enne üleslaadimist).
        $mime = $file->getMimeType();
        $w = imagesx($img);
        $h = imagesy($img);
        if (in_array($mime, ['image/jpeg', 'image/jpg'], true) && $w <= 1920 && $h <= 1920) {
            imagedestroy($img);

            return $file->storeAs('memories', $filename, 'public');
        }

        $resized = $this->resizeImage($img, 1920);
        $fullPath = storage_path('app/public/'.$path);
        @mkdir(dirname($fullPath), 0775, true);
        imagejpeg($resized, $fullPath, 92);
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
        if (! $img) {
            return '';
        }

        // 800 px pisipilt: katab retina-ekraanid (DPR 2) albumi vaates ilma udususeta.
        $resized = $this->resizeImage($img, 800);
        $fullPath = storage_path('app/public/'.$path);
        @mkdir(dirname($fullPath), 0775, true);
        imagejpeg($resized, $fullPath, 82);
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
        if ($w <= $maxDim && $h <= $maxDim) {
            return $img;
        }
        $ratio = min($maxDim / $w, $maxDim / $h);
        $newW = (int) round($w * $ratio);
        $newH = (int) round($h * $ratio);
        $new = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($new, $img, 0, 0, 0, 0, $newW, $newH, $w, $h);

        return $new;
    }
}
