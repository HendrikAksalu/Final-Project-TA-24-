<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlbumShareController extends Controller
{
    public function index(Request $request, Album $album): JsonResponse
    {
        if (! $album->userCanAccess($request->user())) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($album->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Ainult omanik näeb jagamise nimekirja.'], 403);
        }

        $rows = $album->collaborators()->get();

        return response()->json([
            'collaborators' => $rows->map(fn (User $u) => [
                'userId' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->pivot->role,
            ])->all(),
        ]);
    }

    public function store(Request $request, Album $album): JsonResponse
    {
        if ($album->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Ainult albumi omanik saab jagada.'], 403);
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['nullable', 'string', 'in:editor,viewer'],
        ]);

        $role = $validated['role'] ?? 'editor';

        $target = User::query()->where('email', $validated['email'])->first();
        if (! $target) {
            return response()->json([
                'message' => 'Selle e-postiga kasutajat ei leitud. Kasutaja peab esmalt registreeruma.',
            ], 422);
        }

        if ($target->id === $album->user_id) {
            return response()->json(['message' => 'Omanikuga ei pea albumit jagama.'], 422);
        }

        $album->collaborators()->syncWithoutDetaching([
            $target->id => ['role' => $role],
        ]);

        return response()->json(['message' => 'Album jagatud.']);
    }

    public function destroy(Request $request, Album $album, User $user): JsonResponse
    {
        if ($album->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Ainult albumi omanik saab jagamise tühistada.'], 403);
        }

        $album->collaborators()->detach($user->id);

        return response()->json(['message' => 'Jagamine eemaldatud.']);
    }

    public function leave(Request $request, Album $album): JsonResponse
    {
        $currentUser = $request->user();
        if (! $album->userCanAccess($currentUser)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }
        if ($album->user_id === $currentUser->id) {
            return response()->json(['message' => 'Omanik ei saa albumist lahkuda.'], 422);
        }

        $album->collaborators()->detach($currentUser->id);

        return response()->json(['message' => 'Lahkusid albumist.']);
    }
}
