<?php

namespace App\Http\Controllers\Admin\JobVacancy;

use App\Http\Controllers\Controller;
use App\Models\CareerInformation;
use Illuminate\Http\Request;

class JobVacancyController extends Controller
{
    /**
     * List semua loker (admin view)
     */
    public function index(Request $request)
    {
        $data = CareerInformation::with(['creator:id,name,email', 'approver:id,name'])
            ->where('info_type', 'job_vacancy')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Detail loker
     */
    public function show($id)
    {
        $data = CareerInformation::with(['creator:id,name,email', 'approver:id,name'])
            ->where('info_type', 'job_vacancy')
            ->find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Approve loker
     */
    public function approve($id, Request $request)
    {
        $data = CareerInformation::find($id);

        if (!$data || $data->info_type !== 'job_vacancy') {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        if ($data->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Loker sudah aktif'
            ], 400);
        }

        $data->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Loker berhasil disetujui'
        ]);
    }

    /**
     * Reject / End loker
     */
    public function reject($id)
    {
        $data = CareerInformation::find($id);

        if (!$data || $data->info_type !== 'job_vacancy') {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $data->update([
            'status' => 'ended',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Loker berhasil ditolak / diakhiri'
        ]);
    }
}
