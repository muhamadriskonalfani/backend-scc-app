<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AdminProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminManagementController extends Controller
{
    public function registerAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',

            'faculty_id' => 'required|exists:faculties,id',
            'nip' => 'nullable|string|max:30',
            'position' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();

        try {
            // 1. Buat user admin
            $admin = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'admin',
                'status' => 'active',
            ]);

            // 2. Buat admin profile (scope fakultas)
            AdminProfile::create([
                'user_id' => $admin->id,
                'faculty_id' => $request->faculty_id,
                'nip' => $request->nip,
                'position' => $request->position,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Admin fakultas berhasil dibuat',
                'data' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'faculty_id' => $request->faculty_id,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat admin',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
