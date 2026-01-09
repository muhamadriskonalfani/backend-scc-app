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
    public function index()
    {
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
            'total' => CareerInformation::where('info_type', 'job_vacancy')->count(),
            'pending' => CareerInformation::where('info_type', 'job_vacancy')->where('status', 'pending')->count(),
            'active' => CareerInformation::where('info_type', 'job_vacancy')->where('status', 'active')->count(),
            'ended' => CareerInformation::where('info_type', 'job_vacancy')->where('status', 'ended')->count(),
        ];

        // ================= APPRENTICESHIP =================
        $apprenticeship = [
            'total' => CareerInformation::where('info_type', 'apprenticeship')->count(),
            'pending' => CareerInformation::where('info_type', 'apprenticeship')->where('status', 'pending')->count(),
            'active' => CareerInformation::where('info_type', 'apprenticeship')->where('status', 'active')->count(),
            'ended' => CareerInformation::where('info_type', 'apprenticeship')->where('status', 'ended')->count(),
        ];

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
            ]
        ]);
    }
}
