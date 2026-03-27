<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FacultyController extends Controller
{
    /**
     * List fakultas
     */
    public function index(Request $request)
    {
        $query = Faculty::query();

        $search = $request->search;
        
        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $faculties = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $faculties
        ]);
    }

    /**
     * Create fakultas
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:faculties,name',
            'code' => 'required|string|max:10|unique:faculties,code',
        ]);

        $faculty = Faculty::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fakultas berhasil dibuat',
            'data' => $faculty
        ], 201);
    }

    /**
     * Edit fakultas
     */
    public function show(Request $request, $id)
    {
        $faculty = Faculty::find($id);

        if (!$faculty) {
            return response()->json([
                'success' => false,
                'message' => 'Fakultas tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $faculty,
        ]);
    }

    /**
     * Update fakultas
     */
    public function update(Request $request, $id)
    {
        $faculty = Faculty::find($id);

        if (!$faculty) {
            return response()->json([
                'message' => 'Fakultas tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('faculties', 'name')->ignore($faculty->id),
            ],
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('faculties', 'code')->ignore($faculty->id),
            ],
        ]);

        $faculty->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fakultas berhasil diperbarui',
            'data' => $faculty
        ]);
    }

    /**
     * Delete fakultas
     */
    public function destroy($id)
    {
        $faculty = Faculty::withCount([
            'studyPrograms',
            'tracerStudies',
            'adminProfiles'
        ])->find($id);

        if (!$faculty) {
            return response()->json([
                'message' => 'Fakultas tidak ditemukan'
            ], 404);
        }

        if (
            $faculty->study_programs_count > 0 ||
            $faculty->tracer_studies_count > 0 ||
            $faculty->admin_profiles_count > 0
        ) {
            return response()->json([
                'message' => 'Fakultas tidak dapat dihapus karena masih digunakan'
            ], 400);
        }

        $faculty->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fakultas berhasil dihapus'
        ]);
    }
}
