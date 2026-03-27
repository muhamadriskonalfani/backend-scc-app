<?php

namespace App\Http\Controllers\Admin\TracerStudy;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\StudyProgram;
use App\Models\TracerStudy;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TracerStudyExport;
use Barryvdh\DomPDF\Facade\Pdf;

class TracerStudyController extends Controller
{
    /**
     * Centralized Filter Query
     */
    private function filteredQuery(Request $request)
    {
        $query = TracerStudy::query()
            ->with([
                'user:id,name,email',
                'faculty:id,name',
                'studyProgram:id,name'
            ]);

        // SEARCH
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('student_id_number', 'like', "%{$search}%");
            });
        }

        // FAKULTAS
        if ($request->filled('faculty_id')) {
            $query->where('faculty_id', $request->faculty_id);
        }

        // PRODI
        if ($request->filled('study_program_id')) {
            $query->where('study_program_id', $request->study_program_id);
        }

        // RANGE ENTRY YEAR
        if ($request->filled('entry_year_from') && $request->filled('entry_year_to')) {
            $query->whereBetween('entry_year', [
                $request->entry_year_from,
                $request->entry_year_to
            ]);
        } elseif ($request->filled('entry_year_from')) {
            $query->where('entry_year', '>=', $request->entry_year_from);
        } elseif ($request->filled('entry_year_to')) {
            $query->where('entry_year', '<=', $request->entry_year_to);
        }

        return $query
            ->orderByDesc('entry_year')
            ->orderBy('student_id_number');
    }

    /**
     * INDEX
     */
    public function index(Request $request)
    {
        $currentYear = date('Y');

        $request->validate([
            'faculty_id' => 'nullable|exists:faculties,id',
            'study_program_id' => 'nullable|exists:study_programs,id',
            'entry_year_from' => "nullable|integer|digits:4|min:2016|max:$currentYear",
            'entry_year_to' => "nullable|integer|digits:4|min:2016|max:$currentYear",
            'search' => 'nullable|string|max:100'
        ]);

        $data = $this->filteredQuery($request)->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $data,
            'filters' => $request->all(),
            'faculties' => Faculty::orderBy('name')->get(['id','name']),
            'study_programs' => StudyProgram::orderBy('name')->get(['id','name']),
        ]);
    }

    /**
     * EXPORT
     */
    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:excel,pdf'
        ]);

        $query = $this->filteredQuery($request);

        $data = $query->get();

        if ($request->type === 'pdf') {

            $pdf = PDF::loadView('pdf.tracer-study', [
                'data' => $data,
                'filters' => $request->only([
                    'search',
                    'faculty_id',
                    'study_program_id',
                    'entry_year_from',
                    'entry_year_to'
                ])
            ])->setPaper('A4', 'landscape');

            return $pdf->download('tracer-study.pdf');
        }

        return Excel::download(
            new TracerStudyExport($data),
            'tracer_study.xlsx'
        );
    }

    /**
     * SHOW
     */
    public function show($id)
    {
        $tracer = TracerStudy::with([
                'user:id,name,email',
                'user.profile:id,user_id,gender',
                'faculty:id,name',
                'studyProgram:id,name'
            ])
            ->find($id);

        // Jika tidak ditemukan
        if (!$tracer) {
            return response()->json([
                'success' => false,
                'message' => 'Data tracer study tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tracer,
        ]);
    }
}
