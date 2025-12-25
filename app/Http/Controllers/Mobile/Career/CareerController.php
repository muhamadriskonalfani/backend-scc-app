<?php

namespace App\Http\Controllers\Mobile\Career;

use App\Http\Controllers\Controller;
use App\Models\CareerInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CareerController extends Controller
{
    /**
     * List career information (student & alumni)
     */
    public function index()
    {
        $careers = CareerInformation::where('status', 'approved')
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
            'message' => $careers->count() > 0
                ? 'Data lowongan berhasil diambil'
                : 'Belum ada lowongan tersedia',
            'data' => $careers
        ]);
    }

    /**
     * Detail career information
     */
    public function show($id)
    {
        $career = CareerInformation::with('creator:id,name')
            ->where('id', $id)
            ->where('status', 'approved')
            ->first();

        if (!$career) {
            return response()->json([
                'success' => false,
                'message' => 'Informasi lowongan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail lowongan berhasil diambil',
            'data' => $career
        ]);
    }

    /**
     * Alumni: create career info
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'info_type' => 'required|in:job_vacancy,apprenticeship',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'company_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'expired_at' => 'nullable|date|after:today',
            'image' => 'nullable|string',
        ]);

        $career = CareerInformation::create([
            ...$validated,
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil dibuat dan menunggu persetujuan admin',
            'data' => $career
        ], 201);
    }

    /**
     * Alumni: update career (ownership protected)
     */
    public function update(Request $request, $id)
    {
        $career = CareerInformation::where('id', $id)
            ->where('created_by', Auth::id())
            ->first();

        if (!$career) {
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

        $career->update([
            ...$validated,
            'status' => 'pending',
            'approved_by' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil diperbarui dan menunggu persetujuan admin',
            'data' => $career
        ]);
    }

    /**
     * Alumni: list career milik sendiri
     */
    public function myCareers()
    {
        $careers = CareerInformation::where('created_by', Auth::id())
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => $careers->count() > 0
                ? 'Data lowongan Anda berhasil diambil'
                : 'Anda belum memiliki lowongan',
            'data' => $careers
        ]);
    }
}
