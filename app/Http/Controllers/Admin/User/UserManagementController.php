<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Profile;
use App\Models\StudyProgram;
use App\Models\TracerStudy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Lists Student / Alumni
     */
    public function index(Request $request)
    {
        $currentYear = date('Y');

        $request->validate([
            'search'            => 'nullable|string|max:100',
            'type'              => 'nullable|in:student,alumni',
            'faculty_id'        => 'nullable|exists:faculties,id',
            'study_program_id'  => 'nullable|exists:study_programs,id',
            'entry_year_from'   => "nullable|integer|digits:4|min:2016|max:$currentYear",
            'entry_year_to'     => "nullable|integer|digits:4|min:2016|max:$currentYear",
        ]);

        $query = User::query()
            ->whereIn('role', ['student', 'alumni'])
            ->with([
                'tracerStudy:id,user_id,student_id_number,faculty_id,study_program_id,entry_year,graduation_year',
                'tracerStudy.studyProgram:id,name',
                'tracerStudy.faculty:id,name',
            ]);

        // 🔍 SEARCH
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhereHas('tracerStudy', function ($sub) use ($search) {
                    $sub->where('student_id_number', 'like', "%{$search}%");
                });
            });
        }

        // 🔍 FILTER ROLE
        if ($request->filled('type')) {
            $query->where('role', $request->type);
        }

        // 🔍 FILTER FACULTY
        if ($request->filled('faculty_id')) {
            $query->whereHas('tracerStudy', function ($q) use ($request) {
                $q->where('faculty_id', $request->faculty_id);
            });
        }

        // 🔍 FILTER PRODI
        if ($request->filled('study_program_id')) {
            $query->whereHas('tracerStudy', function ($q) use ($request) {
                $q->where('study_program_id', $request->study_program_id);
            });
        }

        // 🔍 FILTER ENTRY YEAR
        if ($request->filled('entry_year_from') || $request->filled('entry_year_to')) {
            $query->whereHas('tracerStudy', function ($q) use ($request) {

                if ($request->filled('entry_year_from') && $request->filled('entry_year_to')) {
                    $q->whereBetween('entry_year', [
                        $request->entry_year_from,
                        $request->entry_year_to
                    ]);
                } elseif ($request->filled('entry_year_from')) {
                    $q->where('entry_year', '>=', $request->entry_year_from);
                } elseif ($request->filled('entry_year_to')) {
                    $q->where('entry_year', '<=', $request->entry_year_to);
                }

            });
        }

        $users = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $users,
            'faculties' => Faculty::orderBy('name')->get(['id','name']),
            'study_programs' => StudyProgram::orderBy('name')->get(['id','name']),
        ]);
    }

    /**
     * Detail Student / Alumni 
     */
    public function show($id, Request $request)
    {
        $authUser = $request->user();

        $user = User::with([
                'tracerStudy.faculty:id,name',
                'tracerStudy.studyProgram:id,name,degree',
            ])
            ->whereIn('role', ['student', 'alumni'])
            ->where('id', $id)
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        // 🎯 Tentukan apakah satu fakultas
        $isSameFaculty = false;

        if ($authUser->role === 'admin') {
            $adminFacultyId = $authUser->adminProfile->faculty_id;
            $userFacultyId = optional($user->tracerStudy)->faculty_id;

            $isSameFaculty = $adminFacultyId === $userFacultyId;
        }

        return response()->json([
            'success' => true,
            'is_same_faculty' => $isSameFaculty,
            'data' => $user,
        ]);
    }

    /**
     * Register Student / Alumni by Admin
     */
    public function store(Request $request)
    {
        $request->validate([
            // user
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['student', 'alumni'])],
            'gender' => ['required', Rule::in(['male','female'])],

            // tracer study
            'student_id_number' => 'required|string|max:50|unique:tracer_studies,student_id_number',
            'faculty_id' => 'required|exists:faculties,id',
            'study_program_id' => 'required|exists:study_programs,id',
            'entry_year' => 'required|digits:4|integer|min:1990|max:' . date('Y'),
            'graduation_year' => 'nullable|digits:4|integer|gte:entry_year',
        ]);

        $studyProgram = StudyProgram::where('id', $request->study_program_id)
            ->where('faculty_id', $request->faculty_id)
            ->first();

        if (!$studyProgram) {
            return response()->json([
                'message' => 'Program studi tidak sesuai dengan fakultas'
            ], 422);
        }

        DB::beginTransaction();

        try {
            // 1. Simpan ke Users
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'status' => 'active',
            ]);

            // 2. Simpan ke Tracer Study
            TracerStudy::create([
                'user_id' => $user->id,
                'full_name' => $request->name,
                'student_id_number' => $request->student_id_number,
                'faculty_id' => $request->faculty_id,
                'study_program_id' => $request->study_program_id,
                'entry_year' => $request->entry_year,
                'graduation_year' => $request->graduation_year,
            ]);

            // 3. Simpan ke Profile
            Profile::create([
                'user_id' => $user->id,
                'gender' => $request->gender,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Pengguna berhasil ditambahkan.',
                'status' => 'active',
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Exception: Registrasi gagal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve user
     */
    public function approve($id, Request $request)
    {
        $authUser = $request->user();

        // 🔐 Hanya admin
        if ($authUser->role !== 'admin') {
            return response()->json([
                'message' => 'Hanya admin yang dapat melakukan aksi ini'
            ], 403);
        }

        $user = $this->findUserByFaculty($id, $authUser);

        if ($user->status === 'active') {
            return response()->json([
                'message' => 'User sudah aktif'
            ], 400);
        }

        $user->update([
            'status' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil disetujui'
        ]);
    }

    /**
     * Reject user
     */
    public function reject($id, Request $request)
    {
        $authUser = $request->user();

        // 🔐 Hanya admin
        if ($authUser->role !== 'admin') {
            return response()->json([
                'message' => 'Hanya admin yang dapat melakukan aksi ini'
            ], 403);
        }

        $user = $this->findUserByFaculty($id, $authUser);

        $user->update([
            'status' => 'rejected'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil ditolak'
        ]);
    }

    /**
     * ==========================
     * HELPER FUNCTIONS
     * ==========================
     */

    private function findUserByFaculty(int $userId, $authUser)
    {
        $facultyId = $authUser->adminProfile?->faculty_id;

        if (!$facultyId) {
            abort(response()->json([
                'message' => 'Admin tidak memiliki faculty_id'
            ], 400));
        }

        $user = User::whereIn('role', ['student', 'alumni'])
            ->where('id', $userId)
            ->whereHas('tracerStudy', function ($q) use ($facultyId) {
                $q->where('faculty_id', $facultyId);
            })
            ->with('tracerStudy')
            ->first();

        if (!$user) {
            abort(response()->json([
                'message' => 'User tidak ditemukan atau bukan dari fakultas Anda'
            ], 404));
        }

        return $user;
    }
}
