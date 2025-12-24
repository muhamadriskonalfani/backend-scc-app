<?php

namespace App\Http\Controllers\Mobile\TracerStudy;

use App\Http\Controllers\Controller;
use App\Models\TracerStudy;
use Illuminate\Http\Request;

class TracerStudyController extends Controller
{
    /**
     * Get tracer study milik user login
     * (untuk halaman tracer study - read only)
     */
    public function index(Request $request)
    {
        $tracerStudy = TracerStudy::where('user_id', $request->user()->id)
            ->with(['faculty:id,name', 'studyProgram:id,name'])
            ->first();

        if (!$tracerStudy) {
            return response()->json([
                'message' => 'Tracer study tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'data' => $tracerStudy
        ]);
    }

    /**
     * Update tracer study (lengkapi data)
     */
    public function update(Request $request)
    {
        $request->validate([
            'domicile' => 'required|string|max:100',
            'whatsapp_number' => 'required|string|max:20',

            'current_workplace' => 'nullable|string|max:150',
            'current_job_duration_months' => 'nullable|integer|min:0',
            'company_scale' => 'nullable|in:local,national,international',
            'job_title' => 'nullable|string|max:100',
        ]);

        $tracerStudy = TracerStudy::where('user_id', $request->user()->id)->first();

        if (!$tracerStudy) {
            return response()->json([
                'message' => 'Tracer study tidak ditemukan'
            ], 404);
        }

        $tracerStudy->update([
            'domicile' => $request->domicile,
            'whatsapp_number' => $request->whatsapp_number,
            'current_workplace' => $request->current_workplace,
            'current_job_duration_months' => $request->current_job_duration_months,
            'company_scale' => $request->company_scale,
            'job_title' => $request->job_title,
        ]);

        return response()->json([
            'message' => 'Tracer study berhasil diperbarui',
            'data' => $tracerStudy
        ]);
    }

    /**
     * (Opsional) cek status kelengkapan tracer study
     */
    public function status(Request $request)
    {
        $tracerStudy = TracerStudy::where('user_id', $request->user()->id)->first();

        if (!$tracerStudy) {
            return response()->json([
                'message' => 'Tracer study tidak ditemukan'
            ], 404);
        }

        $requiredFields = [
            'domicile',
            'whatsapp_number',
            'current_workplace',
            'job_title',
            'company_scale',
        ];

        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (empty($tracerStudy->$field)) {
                $missingFields[] = $field;
            }
        }

        return response()->json([
            'completed' => count($missingFields) === 0,
            'missing_fields' => $missingFields
        ]);
    }
}
