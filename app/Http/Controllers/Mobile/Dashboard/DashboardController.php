<?php

namespace App\Http\Controllers\Mobile\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CampusInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Ambil data dashboard user
     */
    public function index()
    {
        $user = Auth::user();

        $profile = $user->profile;
        $tracer  = $user->tracerStudy;

        $facultyId = $tracer?->faculty_id;

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'gender' => $profile?->gender,
            'photo' => $profile?->image,
            'student_id_number' => $tracer?->student_id_number,
        ];

        $campusInfo = CampusInformation::query()
            ->where('status', 'active')
            ->where(function ($query) use ($facultyId) {
                $query->whereNull('faculty_id');

                if ($facultyId) {
                    $query->orWhere('faculty_id', $facultyId);
                }
            })
            ->latest()
            ->limit(5)
            ->get([
                'id',
                'title',
                'image',
                'created_at'
            ]);

        return response()->json([
            'success' => true,
            'user' => $userData,
            'campus_info' => $campusInfo,
        ]);
    }
}
