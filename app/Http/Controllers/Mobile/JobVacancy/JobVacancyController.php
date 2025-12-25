<?php

namespace App\Http\Controllers\Mobile\JobVacancy;

use App\Http\Controllers\Controller;
use App\Models\CareerInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobVacancyController extends Controller
{
    /**
     * List career information (student & alumni)
     */
    public function index()
    {
        $data = CareerInformation::where('info_type', 'job_vacancy')
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
                ? 'Data lowongan berhasil diambil'
                : 'Belum ada lowongan tersedia',
            'data' => $data
        ]);
    }

    /**
     * Detail career information
     */
    public function show($id)
    {
        $data = CareerInformation::with('creator:id,name')
            ->where('id', $id)
            ->where('info_type', 'job_vacancy')
            ->where('status', 'approved')
            ->first();

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Informasi lowongan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail lowongan berhasil diambil',
            'data' => $data
        ]);
    }

    /**
     * Alumni: create career info
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
            'info_type' => 'job_vacancy',
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil dibuat dan menunggu persetujuan admin',
            'data' => $data
        ], 201);
    }

    /**
     * Alumni: update career (ownership protected)
     */
    public function update(Request $request, $id)
    {
        $data = CareerInformation::where('id', $id)
            ->where('info_type', 'job_vacancy')
            ->where('created_by', Auth::id())
            ->first();

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Lowongan tidak ditemukan atau bukan milik Anda'
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
            'message' => 'Lowongan berhasil diperbarui dan menunggu persetujuan admin',
            'data' => $data
        ]);
    }

    /**
     * Alumni: list career milik sendiri
     */
    public function myJobvacancy()
    {
        $data = CareerInformation::where('info_type', 'job_vacancy')
            ->where('created_by', Auth::id())
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => $data->count() > 0
                ? 'Data lowongan Anda berhasil diambil'
                : 'Anda belum memiliki lowongan',
            'data' => $data
        ]);
    }
}
