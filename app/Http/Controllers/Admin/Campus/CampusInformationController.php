<?php

namespace App\Http\Controllers\Admin\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusInformation;
use Illuminate\Http\Request;

class CampusInformationController extends Controller
{
    /**
     * List informasi kampus
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $search = $request->search;

        $query = CampusInformation::query();

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
            'image' => 'nullable|string',
            'faculty_id' => 'nullable|exists:faculties,id',
        ]);

        // Admin fakultas TIDAK BOLEH set faculty_id bebas
        if ($user->role === 'admin') {
            $validated['faculty_id'] = $user->adminProfile->faculty_id;
        }

        $campusInfo = CampusInformation::create([
            ...$validated,
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Informasi kampus berhasil dibuat',
            'data' => $campusInfo
        ], 201);
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
        if ($user->role === 'admin' &&
            $campusInfo->faculty_id !== $user->adminProfile->faculty_id
        ) {
            return response()->json([
                'message' => 'Anda tidak berhak mengedit data ini'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'image' => 'nullable|string',
        ]);

        $campusInfo->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Informasi kampus berhasil diperbarui',
            'data' => $campusInfo
        ]);
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
