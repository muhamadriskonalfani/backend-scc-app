<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AdminProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminManagementController extends Controller
{
    /**
     * List semua admin fakultas
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->where('role', 'admin')
            ->with([
                'adminProfile.faculty:id,name'
            ]);

        $search = $request->search;

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhereHas('adminProfile', function ($profileQuery) use ($search) {
                        $profileQuery->where('nip', 'like', "%{$search}%");
                });

            });
        }

        $admins = $query->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $admins
        ]);
    }

    /**
     * Detail admin
     */
    public function show($id)
    {
        $admin = User::where('role', 'admin')
            ->with('adminProfile.faculty:id,name')
            ->find($id);

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $admin
        ]);
    }

    /**
     * Register admin fakultas
     */
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

    /**
     * Update admin fakultas
     */
    public function update(Request $request, $id)
    {
        $admin = User::where('role', 'admin')->find($id);

        if (!$admin) {
            return response()->json([
                'message' => 'Admin tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($admin->id),
            ],
            'password' => 'nullable|min:6|confirmed',

            'faculty_id' => 'sometimes|required|exists:faculties,id',
            'nip' => 'nullable|string|max:30',
            'position' => 'nullable|string|max:100',
            'status' => 'sometimes|in:active,banned',
        ]);

        DB::beginTransaction();

        try {
            $admin->update([
                'name' => $request->name ?? $admin->name,
                'email' => $request->email ?? $admin->email,
                'status' => $request->status ?? $admin->status,
            ]);

            if ($request->password) {
                $admin->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            if ($admin->adminProfile) {
                $admin->adminProfile->update([
                    'faculty_id' => $request->faculty_id ?? $admin->adminProfile->faculty_id,
                    'nip' => $request->nip ?? $admin->adminProfile->nip,
                    'position' => $request->position ?? $admin->adminProfile->position,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Admin berhasil diperbarui'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Gagal update admin',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
