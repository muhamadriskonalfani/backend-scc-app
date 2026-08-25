<?php

namespace App\Http\Controllers\Mobile\TracerStudy;

use App\Http\Controllers\Controller;
use App\Mail\TracerStudySubmittedMail;
use App\Models\TracerStudy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TracerStudyController extends Controller
{
    /**
     * Get tracer study milik user login
     * (untuk halaman tracer study - read only)
     */
    public function index(Request $request)
    {
        try {
            $tracerStudy = TracerStudy::with([
                    'user:id,name,role',
                    'user.profile:id,user_id,gender,image,domicile,phone',
                    'faculty:id,name', 
                    'studyProgram:id,name'
                ])
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$tracerStudy) {
                return response()->json([
                    'message' => 'Tracer study tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'image' => $tracerStudy->user->profile?->image,
                    'gender' => $tracerStudy->user->profile?->gender,
                    'name' => $tracerStudy->user->name,
                    'role' => $tracerStudy->user->role,
                    'nim' => $tracerStudy->student_id_number,

                    'faculty' => $tracerStudy->faculty->name,
                    'study_program' => $tracerStudy->studyProgram->name,
                    'entry_year' => $tracerStudy->entry_year,
                    'graduation_year' => $tracerStudy->graduation_year,
                    'domicile' => $tracerStudy->user->profile?->domicile,
                    'phone' => $tracerStudy->user->profile?->phone,

                    'employment_status' => $tracerStudy->employment_status,
                    'current_workplace' => $tracerStudy->current_workplace,
                    'company_scale' => $tracerStudy->company_scale,
                    'job_title' => $tracerStudy->job_title,
                    'job_category' => $tracerStudy->job_category,
                    'employment_type' => $tracerStudy->employment_type,
                    'employment_sector' => $tracerStudy->employment_sector,
                    'monthly_income_range' => $tracerStudy->monthly_income_range,
                    'job_study_relevance_level' => $tracerStudy->job_study_relevance_level,
                    'suggestion_for_university' => $tracerStudy->suggestion_for_university,
                ]
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Exception: Gagal mengambil data tracer study',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update tracer study (lengkapi data)
     */
    public function update(Request $request)
    {
        $request->validate([
            'employment_status' => 'nullable|in:bekerja,wirausaha,lanjut studi,belum bekerja',
            'current_workplace' => 'nullable|string|max:255',
            'company_scale' => 'nullable|in:local,national,international',
            'job_title' => 'nullable|string|max:255',
            'job_category' => 'nullable|in:formal,informal,wirausaha,freelance',
            'employment_type' => 'nullable|in:full-time,part-time,kontrak,magang',
            'employment_sector' => 'nullable|in:pendidikan,IT,keuangan,manufaktur,lainnya',
            'monthly_income_range' => 'nullable|string|max:255',
            'job_study_relevance_level' => 'nullable|in:sangat sesuai,sesuai,kurang,tidak sesuai',
            'suggestion_for_university' => 'nullable|string|max:500',
        ]);

        try {
            $tracerStudy = TracerStudy::with(['user:id,name,email'])
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$tracerStudy) {
                return response()->json([
                    'message' => 'Tracer study tidak ditemukan'
                ], 404);
            }

            $tracerStudy->update([
                'employment_status' => $request->employment_status,
                'current_workplace' => $request->current_workplace,
                'company_scale' => $request->company_scale,
                'job_title' => $request->job_title,
                'job_category' => $request->job_category,
                'employment_type' => $request->employment_type,
                'employment_sector' => $request->employment_sector,
                'monthly_income_range' => $request->monthly_income_range,
                'job_study_relevance_level' => $request->job_study_relevance_level,
                'suggestion_for_university' => $request->suggestion_for_university,
            ]);

            // NOTIFIKASI EMAIL 
            Mail::to($tracerStudy->user->email)->send(
                new TracerStudySubmittedMail($tracerStudy->user)
            );

            return response()->json([
                'message' => 'Tracer study berhasil diperbarui',
                'data' => $tracerStudy
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Exception: Gagal memperbarui tracer study',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
