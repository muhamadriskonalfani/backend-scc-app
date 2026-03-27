<?php

namespace App\Http\Controllers\Admin\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

class CampusInformationController extends Controller
{
    /**
     * List informasi kampus
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $search = $request->search;

        $query = CampusInformation::with([
            'faculty:id,name',
        ]);

        // Admin fakultas → hanya fakultas sendiri & global
        if ($user->role === 'admin') {
            $facultyId = $user->adminProfile->faculty_id;

            $query->where(function ($q) use ($facultyId) {
                $q->whereNull('faculty_id')
                  ->orWhere('faculty_id', $facultyId);
            });
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        $campusInfos = $query
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $campusInfos
        ]);
    }

    /**
     * Create informasi kampus
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'faculty_id' => 'nullable|exists:faculties,id',
        ]);

        try {
            $imagePath = null;

            // Upload image
            if ($request->hasFile('image')) {
                $filename = 'campus_' . Str::random(20) . '.webp';
                $path = 'assets/campus';

                $image = ImageManager::imagick()
                    ->read($request->file('image')->getPathname())
                    ->scaleDown(width: 1200)
                    ->toWebp(85);

                Storage::disk('public')->put(
                    $path . '/' . $filename,
                    (string) $image
                );

                $imagePath = $path . '/' . $filename;
            }

            // Force faculty untuk admin
            if ($user->role === 'admin') {
                $validated['faculty_id'] = $user->adminProfile->faculty_id;
            }

            $campusInfo = CampusInformation::create([
                ...$validated,
                'image' => $imagePath,
                'status' => 'active',
                'created_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Informasi kampus berhasil dibuat',
                'data' => $campusInfo
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat informasi kampus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detail informasi kampus
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $query = CampusInformation::with([
            'faculty:id,name',
            'user:id,name,email'
        ]);

        // Admin fakultas → hanya fakultas sendiri & global
        if ($user->role === 'admin') {
            $facultyId = $user->adminProfile->faculty_id;

            $query->where(function ($q) use ($facultyId) {
                $q->whereNull('faculty_id')
                ->orWhere('faculty_id', $facultyId);
            });
        }

        $campus = $query->find($id);

        if (!$campus) {
            return response()->json([
                'success' => false,
                'message' => 'Informasi kampus tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $campus
        ]);
    }

    /**
     * Update informasi kampus
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $campusInfo = CampusInformation::find($id);

        if (!$campusInfo) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        // Cek ownership
        if (
            $user->role === 'admin' &&
            $campusInfo->faculty_id !== $user->adminProfile->faculty_id
        ) {
            return response()->json([
                'message' => 'Anda tidak berhak mengedit data ini'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'sometimes|in:active,ended',
        ]);

        try {
            // Upload image baru
            if ($request->hasFile('image')) {

                // Hapus image lama
                if ($campusInfo->image && Storage::disk('public')->exists($campusInfo->image)) {
                    Storage::disk('public')->delete($campusInfo->image);
                }

                $filename = 'campus_' . Str::random(20) . '.webp';
                $path = 'assets/campus';

                $image = ImageManager::imagick()
                    ->read($request->file('image')->getPathname())
                    ->scaleDown(width: 1200)
                    ->toWebp(85);

                Storage::disk('public')->put(
                    $path . '/' . $filename,
                    (string) $image
                );

                $validated['image'] = $path . '/' . $filename;
            }

            $campusInfo->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Informasi kampus berhasil diperbarui',
                'data' => $campusInfo
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui informasi kampus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * End / nonaktifkan informasi kampus
     */
    public function end($id, Request $request)
    {
        $user = $request->user();
        $campusInfo = CampusInformation::find($id);

        if (!$campusInfo) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        // Cek ownership
        if ($user->role === 'admin' &&
            $campusInfo->faculty_id !== $user->adminProfile->faculty_id
        ) {
            return response()->json([
                'message' => 'Anda tidak berhak mengakhiri data ini'
            ], 403);
        }

        $campusInfo->update([
            'status' => 'ended'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Informasi kampus berhasil diakhiri'
        ]);
    }
}
