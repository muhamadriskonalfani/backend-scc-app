<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CampusInformation;
use App\Models\CareerInformation;
use App\Models\Faculty;
use App\Models\StudyProgram;
use App\Models\TracerStudy;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Pengecekan Role 
        // if (Auth::user()->role == 'admin')
        // if (in_array(Auth::user()->role, ['admin', 'super_admin']))

        // ================= USERS =================
        $users = [
            'total' => User::count(),

            'admin' => [
                'total' => User::whereIn('role', ['admin', 'super_admin'])->count(),
                'super_admin' => User::where('role', 'super_admin')->count(),
                'admin' => User::where('role', 'admin')->count(),
            ],

            'alumni' => [
                'total' => User::where('role', 'alumni')->count(),
                'pending' => User::where('role', 'alumni')->where('status', 'pending')->count(),
                'active' => User::where('role', 'alumni')->where('status', 'active')->count(),
                'banned' => User::where('role', 'alumni')->where('status', 'banned')->count(),
            ],

            'student' => [
                'total' => User::where('role', 'student')->count(),
                'pending' => User::where('role', 'student')->where('status', 'pending')->count(),
                'active' => User::where('role', 'student')->where('status', 'active')->count(),
                'banned' => User::where('role', 'student')->where('status', 'banned')->count(),
            ],
        ];


        // ================= TRACER STUDY =================
        $tracerStudy = [
            'total' => TracerStudy::count(),
        ];


        // ================= JOB VACANCY =================
        $jobVacancy = [
            'pending' => CareerInformation::where('info_type', 'job_vacancy')->where('status', 'pending')->count(),
            'approved' => CareerInformation::where('info_type', 'job_vacancy')->where('status', 'approved')->count(),
        ];
        $jobVacancy['total'] = $jobVacancy['pending'] + $jobVacancy['approved'];

        // ================= APPRENTICESHIP =================
        $apprenticeship = [
            'pending' => CareerInformation::where('info_type', 'apprenticeship')->where('status', 'pending')->count(),
            'approved' => CareerInformation::where('info_type', 'apprenticeship')->where('status', 'approved')->count(),
        ];
        $apprenticeship['total'] = $apprenticeship['pending'] + $apprenticeship['approved'];

        // ================= CAMPUS INFO =================
        $campusInfo = [
            'total' => CampusInformation::count(),
            'active' => CampusInformation::where('status', 'active')->count(),
            'ended' => CampusInformation::where('status', 'ended')->count(),
        ];

        // ================= FACULTY =================
        $faculty = [
            'total' => Faculty::count(),
            // 'active' => Faculty::where('status', 'active')->count(),
            // 'inactive' => Faculty::where('status', 'inactive')->count(),
        ];

        // ================= STUDY PROGRAM =================
        $studyProgram = [
            'total' => StudyProgram::count(),
            // 'active' => StudyProgram::where('status', 'active')->count(),
            // 'inactive' => StudyProgram::where('status', 'inactive')->count(),
        ];


        // ================================================
        // USER CHART
        // ================================================
        $entryYear = $request->input('entry_year');
        $userChart = [
            'entry_year' => $entryYear,

            'alumni' => TracerStudy::join('users', 'users.id', '=', 'tracer_studies.user_id')
                ->where('users.status', 'active')
                ->where('users.role', 'alumni')
                ->when($entryYear, function ($query) use ($entryYear) {
                    $query->where('entry_year', $entryYear);
                })
                ->count(),

            'student' => TracerStudy::join('users', 'users.id', '=', 'tracer_studies.user_id')
                ->where('users.status', 'active')
                ->where('users.role', 'student')
                ->when($entryYear, function ($query) use ($entryYear) {
                    $query->where('entry_year', $entryYear);
                })
                ->count(),
        ];
        $userChart['total'] = $userChart['alumni'] + $userChart['student'];


        // ================================================
        // TRACER STUDY CHART
        // ================================================
        $graduationYear = $request->input('graduation_year');
        $tracerStats = TracerStudy::join('users', 'users.id', '=', 'tracer_studies.user_id')
            ->where('users.status', 'active')
            ->where('users.role', 'alumni')
            ->when($graduationYear, function ($query) use ($graduationYear) {
                    $query->where('graduation_year', $graduationYear);
                })
            ->selectRaw("
                COUNT(*) as total,
                SUM(
                    CASE 
                        WHEN employment_status IS NOT NULL 
                        AND job_title IS NOT NULL
                        AND employment_type IS NOT NULL
                        AND job_study_relevance_level IS NOT NULL
                        THEN 1 ELSE 0 
                    END
                ) as completed,
                SUM(
                    CASE 
                        WHEN employment_status IS NULL 
                        OR job_title IS NULL
                        OR employment_type IS NULL
                        OR job_study_relevance_level IS NULL
                        THEN 1 ELSE 0 
                    END
                ) as incomplete
            ")
            ->first();
            
        $tracerChart = [
            'graduation_year' => $graduationYear,
            'total' => $tracerStats->total,
            'completed' => $tracerStats->completed,
            'incomplete' => $tracerStats->incomplete,
        ];


        return response()->json([
            'success' => true,
            'data' => [
                'users' => $users,
                'tracer_study' => $tracerStudy,
                'job_vacancy' => $jobVacancy,
                'apprenticeship' => $apprenticeship,
                'campus_information' => $campusInfo,
                'faculty' => $faculty,
                'study_program' => $studyProgram,
                'userChart' => $userChart,
                'tracerChart' => $tracerChart,
            ]
        ]);
    }
}
