<?php

namespace App\Http\Controllers\Mobile\Apprenticeship;

use App\Http\Controllers\Controller;
use App\Models\CareerInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprenticeshipController extends Controller
{
    /**
     * List informasi magang (student & alumni)
     */
    public function index()
    {
        $data = CareerInformation::where('info_type', 'apprenticeship')
            ->where('status', 'approved')
            ->latest()
            ->select([
                'id',
                'title',
                'company_name',
                'location',
                'image',
                'created_at'
            ])
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => $data->count() > 0
                ? 'Data magang berhasil diambil'
                : 'Belum ada informasi magang',
            'data' => $data
        ]);
    }

    /**
     * Detail informasi magang
     */
    public function show($id)
    {
        $data = CareerInformation::with('creator:id,name')
            ->where('id', $id)
            ->where('info_type', 'apprenticeship')
            ->where('status', 'approved')
            ->first();

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Informasi magang tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail magang berhasil diambil',
            'data' => $data
        ]);
    }

    /**
     * Alumni: create info magang
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'company_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'expired_at' => 'nullable|date|after:today',
            'image' => 'nullable|string',
        ]);

        $data = CareerInformation::create([
            ...$validated,
            'info_type' => 'apprenticeship',
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Informasi magang berhasil dibuat dan menunggu persetujuan admin',
            'data' => $data
        ], 201);
    }

    /**
     * Alumni: update info magang milik sendiri
     */
    public function update(Request $request, $id)
    {
        $data = CareerInformation::where('id', $id)
            ->where('info_type', 'apprenticeship')
            ->where('created_by', Auth::id())
            ->first();

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan atau bukan milik Anda'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'company_name' => 'sometimes|required|string|max:255',
            'location' => 'sometimes|required|string|max:255',
            'expired_at' => 'nullable|date|after:today',
            'image' => 'nullable|string',
        ]);

        $data->update([
            ...$validated,
            'status' => 'pending',
            'approved_by' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Informasi magang berhasil diperbarui dan menunggu persetujuan admin',
            'data' => $data
        ]);
    }

    /**
     * Alumni: list magang milik sendiri
     */
    public function myApprenticeships()
    {
        $data = CareerInformation::where('info_type', 'apprenticeship')
            ->where('created_by', Auth::id())
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => $data->count() > 0
                ? 'Data magang Anda berhasil diambil'
                : 'Anda belum memiliki informasi magang',
            'data' => $data
        ]);
    }
}
