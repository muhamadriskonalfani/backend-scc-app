<?php

namespace App\Http\Controllers\Mobile\Auth;

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
            // user
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role' => ['required', Rule::in(['student', 'alumni'])],

            // tracer study
            'student_id_number' => 'required|string|max:50|unique:tracer_studies,student_id_number',
            'faculty_id' => 'required|exists:faculties,id',
            'study_program_id' => 'required|exists:study_programs,id',
            'entry_year' => 'required|digits:4|integer|min:1990|max:' . date('Y'),
            'graduation_year' => 'nullable|digits:4|integer|gte:entry_year',
        ]);

        DB::beginTransaction();

        try {
            // 1. Simpan ke Users
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'status' => 'pending',
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

            DB::commit();

            return response()->json([
                'message' => 'Registrasi berhasil. Akun menunggu persetujuan admin.',
                'status' => 'pending'
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Registrasi gagal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Cari user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        // Cek password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        // Cek status
        if ($user->status === 'pending') {
            return response()->json([
                'message' => 'Akun Anda masih menunggu persetujuan admin'
            ], 403);
        }

        if ($user->status === 'rejected') {
            return response()->json([
                'message' => 'Akun Anda ditolak'
            ], 403);
        }

        // Cek role (mobile hanya student & alumni)
        if (!in_array($user->role, ['student', 'alumni'])) {
            return response()->json([
                'message' => 'Role Anda tidak diizinkan login di aplikasi mobile'
            ], 403);
        }

        // Hapus token lama (opsional tapi recommended)
        $user->tokens()->delete();

        // Buat token baru
        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        // Pastikan user terautentikasi
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Hapus token saat ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }
}
