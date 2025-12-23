<?php

namespace App\Http\Controllers\Mobile\Auth;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Profile;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * Ambil data fakultas & prodi untuk halaman register
     */
    public function registerMeta()
    {
        return response()->json([
            'faculties' => Faculty::select('id', 'name')->get(),
            'study_programs' => StudyProgram::select('id', 'faculty_id', 'name')->get(),
        ]);
    }

    /**
     * Register Student / Alumni
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',

            'role' => ['required', Rule::in(['student', 'alumni'])],

            'student_id_number' => 'required|string|max:30|unique:profiles,student_id_number',
            'faculty_id' => 'required|exists:faculties,id',
            'study_program_id' => 'required|exists:study_programs,id',
            'entry_year' => 'required|digits:4|integer|min:1990|max:' . date('Y'),

            'graduation_year' => [
                'nullable',
                'digits:4',
                'integer',
                'gte:entry_year'
            ],
        ]);

        DB::beginTransaction();

        try {
            // 1. Simpan ke users
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'status' => 'pending',
            ]);

            // 2. Simpan ke profiles
            Profile::create([
                'user_id' => $user->id,
                'student_id_number' => $request->student_id_number,
                'faculty_id' => $request->faculty_id,
                'study_program_id' => $request->study_program_id,
                'entry_year' => $request->entry_year,
                'graduation_year' => $request->graduation_year,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Registrasi berhasil. Akun Anda menunggu persetujuan admin.',
                'status' => 'pending'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Registrasi gagal',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
