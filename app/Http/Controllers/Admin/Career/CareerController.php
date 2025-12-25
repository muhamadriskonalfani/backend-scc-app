<?php

namespace App\Http\Controllers\Admin\Career;

use App\Http\Controllers\Controller;
use App\Models\CareerInformation;
use Illuminate\Http\Request;

class CareerController extends Controller
{
    /**
     * List semua loker (admin view)
     */
    public function index(Request $request)
    {
        $careers = CareerInformation::with(['creator:id,name,email', 'approver:id,name'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $careers
        ]);
    }

    /**
     * Detail loker
     */
    public function show($id)
    {
        $career = CareerInformation::with(['creator:id,name,email', 'approver:id,name'])
            ->find($id);

        if (!$career) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $career
        ]);
    }

    /**
     * Approve loker
     */
    public function approve($id, Request $request)
    {
        $career = CareerInformation::find($id);

        if (!$career) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        if ($career->status === 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Loker sudah aktif'
            ], 400);
        }

        $career->update([
            'status' => 'active',
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
        $career = CareerInformation::find($id);

        if (!$career) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $career->update([
            'status' => 'ended',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Loker berhasil ditolak / diakhiri'
        ]);
    }
}
