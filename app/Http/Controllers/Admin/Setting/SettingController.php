<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\StudyProgram;
use App\Models\TracerStudy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    /**
     * Lists Student / Alumni
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->whereIn('role', ['student', 'alumni'])
            ->with([
                'tracerStudy:id,user_id,student_id_number,study_program_id,entry_year,graduation_year',
                'tracerStudy.studyProgram:id,name',
            ]);

        $search = $request->search;

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhereHas('tracerStudy', function ($profileQuery) use ($search) {
                        $profileQuery->where('student_id_number', 'like', "%{$search}%");
                });

            });
        }

        $users = $query->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Register Student / Alumni by Admin
     */
    public function registerUsers(Request $request)
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
}
