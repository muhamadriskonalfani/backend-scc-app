<?php

namespace App\Http\Controllers\Admin\Apprenticeship;

use App\Http\Controllers\Controller;
use App\Models\CareerInformation;
use Illuminate\Http\Request;

class ApprenticeshipController extends Controller
{
    public function index()
    {
        $data = CareerInformation::with(['creator:id,name,email', 'approver:id,name'])
            ->where('info_type', 'apprenticeship')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function show($id)
    {
        $data = CareerInformation::with(['creator:id,name,email', 'approver:id,name'])
            ->where('info_type', 'apprenticeship')
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

    public function approve(Request $request, $id)
    {
        $data = CareerInformation::find($id);

        if (!$data || $data->info_type !== 'apprenticeship') {
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
            'message' => 'Informasi magang berhasil disetujui'
        ]);
    }

    public function reject($id)
    {
        $data = CareerInformation::find($id);

        if (!$data || $data->info_type !== 'apprenticeship') {
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
            'message' => 'Informasi magang ditolak / diakhiri'
        ]);
    }
}
