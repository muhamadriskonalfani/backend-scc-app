<?php

namespace App\Http\Controllers\Mobile\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CampusInformation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // dummy dashboard untuk admin/super_admin  
    private function dummyDashboard(User $user)
    {
        return [
            'id'        => $user->id,
            'name'      => $user->name,
            'role'      => $user->role,

            // tidak dimiliki admin/super_admin
            'gender'    => null,
            'photo'     => null,
            'student_id_number' => null,
            // 'student_id_number' => $user->role === 'super_admin'
            //     ? 'Super Administrator'
            //     : 'Administrator',
        ];
    }
    
    // ambil data dashboard user
    public function index()
    {
        $user = Auth::user();

        if (in_array($user->role, ['admin', 'super_admin'])) {
            $campusInfo = CampusInformation::query()
                ->where('status', 'active')
                ->latest()
                ->limit(5)
                ->get([
                    'id',
                    'title',
                    'image',
                    'description',
                    'created_at'
                ]);

            return response()->json([
                'success' => true,
                'user' => $this->dummyDashboard($user),
                'campus_info' => $campusInfo,
            ]);
        }

        // Data asli student/alumni
        $profile = $user->profile;
        $tracer = $user->tracerStudy;

        $facultyId = $tracer?->faculty_id;

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
            'gender' => $profile?->gender,
            'photo' => $profile?->image,
            'student_id_number' => $tracer?->student_id_number,
        ];

        $campusInfo = CampusInformation::query()
            ->where('status', 'active')
            ->where(function ($query) use ($facultyId) {
                $query->whereNull('faculty_id');

                if ($facultyId !== null) {
                    $query->orWhere('faculty_id', $facultyId);
                }
            })
            ->latest()
            ->limit(5)
            ->get([
                'id',
                'title',
                'image',
                'description',
                'created_at'
            ]);

        return response()->json([
            'success' => true,
            'user' => $userData,
            'campus_info' => $campusInfo,
        ]);
    }
}
