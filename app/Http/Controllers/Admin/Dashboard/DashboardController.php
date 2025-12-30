<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CampusInformation;
use App\Models\CareerInformation;
use App\Models\TracerStudy;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // ================= USER =================
        $users = [
            'total' => User::count(),
            'pending' => User::where('status', 'pending')->count(),
            'active' => User::where('status', 'active')->count(),
            'rejected' => User::where('status', 'rejected')->count(),
            'banned' => User::where('status', 'banned')->count(),
        ];

        // ================= TRACER STUDY =================
        $tracerStudy = [
            'total' => TracerStudy::count(),
        ];

        // ================= CAREER INFO (JOB VACANCY) =================
        $jobVacancy = [
            'total' => CareerInformation::where('info_type', 'job_vacancy')->count(),
            'pending' => CareerInformation::where('info_type', 'job_vacancy')
                ->where('status', 'pending')
                ->count(),
            'active' => CareerInformation::where('info_type', 'job_vacancy')
                ->where('status', 'active')
                ->count(),
            'ended' => CareerInformation::where('info_type', 'job_vacancy')
                ->where('status', 'ended')
                ->count(),
        ];

        // ================= APPRENTICESHIP =================
        $apprenticeship = [
            'total' => CareerInformation::where('info_type', 'apprenticeship')->count(),
            'pending' => CareerInformation::where('info_type', 'apprenticeship')
                ->where('status', 'pending')
                ->count(),
            'active' => CareerInformation::where('info_type', 'apprenticeship')
                ->where('status', 'active')
                ->count(),
            'ended' => CareerInformation::where('info_type', 'apprenticeship')
                ->where('status', 'ended')
                ->count(),
        ];

        // ================= CAMPUS INFO =================
        $campusInfo = [
            'total' => CampusInformation::count(),
            'active' => CampusInformation::where('status', 'active')->count(),
            'ended' => CampusInformation::where('status', 'ended')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $users,
                'tracer_study' => $tracerStudy,
                'job_vacancy' => $jobVacancy,
                'apprenticeship' => $apprenticeship,
                'campus_information' => $campusInfo,
            ]
        ]);
    }
}
