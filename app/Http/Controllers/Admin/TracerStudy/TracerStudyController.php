<?php

namespace App\Http\Controllers\Admin\TracerStudy;

use App\Http\Controllers\Controller;
use App\Models\TracerStudy;
use Illuminate\Http\Request;

class TracerStudyController extends Controller
{
    /**
     * List Tracer Study (Filter by Faculty, Study Program, Entry Year)
     */
    public function index(Request $request)
    {
        $request->validate([
            'faculty_id' => 'nullable|exists:faculties,id',
            'study_program_id' => 'nullable|exists:study_programs,id',
            'entry_year' => 'nullable|digits:4|integer|min:1990|max:' . date('Y'),
        ]);

        // Guard logic: entry_year tidak boleh sendiri
        if ($request->entry_year && !$request->faculty_id) {
            return response()->json([
                'message' => 'Filter entry year harus disertai fakultas'
            ], 422);
        }

        $query = TracerStudy::query()
            ->with([
                'user:id,name,email',
                'faculty:id,name',
                'studyProgram:id,name'
            ]);

        if ($request->faculty_id) {
            $query->where('faculty_id', $request->faculty_id);
        }

        if ($request->study_program_id) {
            $query->where('study_program_id', $request->study_program_id);
        }

        if ($request->entry_year) {
            $query->where('entry_year', $request->entry_year);
        }

        $data = $query
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'filters' => [
                'faculty_id' => $request->faculty_id,
                'study_program_id' => $request->study_program_id,
                'entry_year' => $request->entry_year,
            ],
            'total' => $data->total(),
            'data' => $data
        ]);
    }
}
