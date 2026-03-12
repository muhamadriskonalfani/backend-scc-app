<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\StudyProgram;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudyProgramController extends Controller
{
    /**
     * List semua prodi
     */
    public function index(Request $request)
    {
        $query = StudyProgram::with('faculty:id,name');
        
        $search = $request->search;

        // Optional filter faculty
        if ($request->faculty_id) {
            $query->where('faculty_id', $request->faculty_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $studyPrograms = $query->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $studyPrograms
        ]);
    }

    /**
     * Detail prodi
     */
    public function show($id)
    {
        $studyProgram = StudyProgram::with('faculty:id,name')->find($id);

        if (!$studyProgram) {
            return response()->json([
                'message' => 'Program studi tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $studyProgram
        ]);
    }

    /**
     * Create prodi
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'faculty_id' => 'required|exists:faculties,id',
            'name' => 'required|string|max:100',
            'degree' => ['required', Rule::in(['D3', 'D4', 'S1', 'S2', 'S3'])],
            'code' => 'required|string|max:20|unique:study_programs,code',
        ]);

        $studyProgram = StudyProgram::create($validated);

        return response()->json([
            'message' => 'Program studi berhasil dibuat',
            'data' => $studyProgram
        ], 201);
    }

    /**
     * Update prodi
     */
    public function update(Request $request, $id)
    {
        $studyProgram = StudyProgram::find($id);

        if (!$studyProgram) {
            return response()->json([
                'message' => 'Program studi tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'faculty_id' => 'sometimes|required|exists:faculties,id',
            'name' => 'sometimes|required|string|max:100',
            'degree' => ['sometimes', Rule::in(['D3', 'D4', 'S1', 'S2', 'S3'])],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('study_programs', 'code')->ignore($studyProgram->id),
            ],
        ]);

        $studyProgram->update($validated);

        return response()->json([
            'message' => 'Program studi berhasil diperbarui',
            'data' => $studyProgram
        ]);
    }

    /**
     * Delete prodi
     */
    public function destroy($id)
    {
        $studyProgram = StudyProgram::withCount('tracerStudies')->find($id);

        if (!$studyProgram) {
            return response()->json([
                'message' => 'Program studi tidak ditemukan'
            ], 404);
        }

        if ($studyProgram->tracer_studies_count > 0) {
            return response()->json([
                'message' => 'Program studi tidak dapat dihapus karena masih digunakan tracer study'
            ], 400);
        }

        $studyProgram->delete();

        return response()->json([
            'message' => 'Program studi berhasil dihapus'
        ]);
    }
}
