<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    /**
     * List mahasiswa (student)
     */
    public function students(Request $request)
    {
        return $this->getUsersByRole('student', $request);
    }

    /**
     * List alumni
     */
    public function alumni(Request $request)
    {
        return $this->getUsersByRole('alumni', $request);
    }

    /**
     * Detail mahasiswa / alumni
     */
    public function show($id, Request $request)
    {
        $facultyId = $request->admin_faculty_id;

        $user = User::with([
                'tracerStudy.faculty:id,name',
                'tracerStudy.studyProgram:id,name,degree',
            ])
            ->whereIn('role', ['student', 'alumni'])
            ->where('id', $id)
            ->whereHas('tracerStudy', function ($q) use ($facultyId) {
                $q->where('faculty_id', $facultyId);
            })
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan atau bukan dari fakultas Anda'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Approve user
     */
    public function approve($id, Request $request)
    {
        $user = $this->findUserByFaculty($id, $request);

        if ($user->status === 'active') {
            return response()->json([
                'message' => 'User sudah aktif'
            ], 400);
        }

        $user->update([
            'status' => 'active'
        ]);

        return response()->json([
            'message' => 'User berhasil disetujui'
        ]);
    }

    /**
     * Reject user
     */
    public function reject($id, Request $request)
    {
        $user = $this->findUserByFaculty($id, $request);

        $user->update([
            'status' => 'rejected'
        ]);

        return response()->json([
            'message' => 'User berhasil ditolak'
        ]);
    }

    /**
     * ==========================
     * HELPER FUNCTIONS
     * ==========================
     */

    private function getUsersByRole(string $role, Request $request)
    {
        $facultyId = $request->admin_faculty_id;

        $query = User::where('role', $role)
            ->whereHas('tracerStudy', function ($q) use ($facultyId, $request) {
                $q->where('faculty_id', $facultyId);

                // Filter optional
                if ($request->study_program_id) {
                    $q->where('study_program_id', $request->study_program_id);
                }

                if ($request->entry_year) {
                    $q->where('entry_year', $request->entry_year);
                }

                if ($request->graduation_year) {
                    $q->where('graduation_year', $request->graduation_year);
                }
            });

        // Filter status user
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $users = $query
            ->with([
                'tracerStudy:id,user_id,student_id_number,study_program_id,entry_year,graduation_year',
                'tracerStudy.studyProgram:id,name'
            ])
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    private function findUserByFaculty(int $userId, Request $request)
    {
        $facultyId = $request->admin_faculty_id;

        $user = User::whereIn('role', ['student', 'alumni'])
            ->where('id', $userId)
            ->whereHas('tracerStudy', function ($q) use ($facultyId) {
                $q->where('faculty_id', $facultyId);
            })
            ->first();

        if (!$user) {
            abort(response()->json([
                'message' => 'User tidak ditemukan atau bukan dari fakultas Anda'
            ], 404));
        }

        return $user;
    }
}
