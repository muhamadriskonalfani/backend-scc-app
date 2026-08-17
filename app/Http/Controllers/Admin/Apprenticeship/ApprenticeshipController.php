<?php

namespace App\Http\Controllers\Admin\Apprenticeship;

use App\Http\Controllers\Controller;
use App\Mail\NewCareerInformationMail;
use App\Models\CareerInformation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ApprenticeshipController extends Controller
{
    /**
     * List apprenticeship info (by faculty)
     */
    public function index(Request $request)
    {
        $facultyId = $request->admin_faculty_id;

        $query = CareerInformation::where('info_type', 'apprenticeship')
            ->where('faculty_id', $facultyId);

        // Optional filters
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereHas('creator', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%");
                });
            });
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->company_name) {
            $query->where('company_name', 'like', '%' . $request->company_name . '%');
        }

        if ($request->from_date && $request->to_date) {
            $query->whereBetween('created_at', [
                $request->from_date,
                $request->to_date
            ]);
        }

        $apprenticeships = $query
            ->with([
                'creator:id,name,email',
                'approver:id,name'
            ])
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $apprenticeships
        ]);
    }

    /**
     * Detail apprenticeship
     */
    public function show($id, Request $request)
    {
        $facultyId = $request->admin_faculty_id;

        $apprenticeship = CareerInformation::with([
                'creator:id,name,email',
                'approver:id,name'
            ])
            ->where('id', $id)
            ->where('info_type', 'apprenticeship')
            ->where('faculty_id', $facultyId)
            ->first();

        if (!$apprenticeship) {
            return response()->json([
                'message' => 'Informasi magang tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $apprenticeship
        ]);
    }

    /**
     * Approve apprenticeship
     */
    public function approve($id, Request $request)
    {
        $apprenticeship = $this->findApprenticeship($id, $request);

        // if ($apprenticeship->status === 'approved') {
        //     return response()->json([
        //         'message' => 'Informasi magang sudah aktif'
        //     ], 400);
        // }

        if ($apprenticeship->status !== 'pending') {
            return response()->json([
                'message' => 'Informasi magang sudah diproses'
            ], 400);
        }

        $apprenticeship->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        // NOTIFIKASI EMAIL 
        // Tahun lulus yang dianggap masih baru
        $currentYear = now()->year;
        $minimumGraduationYear = $currentYear - 2;

        // Cari alumni aktif yang baru lulus
        // $alumni = User::where('role', 'alumni')
        //     ->where('status', 'active')
        //     ->whereHas('tracerStudy', function ($query) use ($minimumGraduationYear, $currentYear) {
        //         $query->whereBetween('graduation_year', [
        //             $minimumGraduationYear,
        //             $currentYear
        //         ]);
        //     })
        //     ->get();

        // // Kirim email kepada alumni yang memenuhi kriteria
        // foreach ($alumni as $user) {
        //     Mail::to($user->email)->send(
        //         new NewCareerInformationMail(
        //             $user,
        //             $apprenticeship
        //         )
        //     );
        // }
        $alumni = User::where('role', 'alumni')
            ->where('status', 'active')
            ->whereHas('tracerStudy', function ($query) use ($minimumGraduationYear, $currentYear) {
                $query->whereBetween('graduation_year', [
                    $minimumGraduationYear,
                    $currentYear
                ]);
            })
            ->first();

        if ($alumni) {
            Mail::to($alumni->email)->send(
                new NewCareerInformationMail(
                    $alumni,
                    $apprenticeship
                )
            );
        }

        return response()->json([
            'message' => 'Informasi magang berhasil disetujui'
        ]);
    }

    /**
     * Reject apprenticeship
     */
    public function reject($id, Request $request)
    {
        $apprenticeship = $this->findApprenticeship($id, $request);

        $apprenticeship->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Informasi magang berhasil ditolak'
        ]);
    }

    /**
     * End apprenticeship
     */
    public function end($id, Request $request)
    {
        $apprenticeship = $this->findApprenticeship($id, $request);

        if ($apprenticeship->status !== 'approved') {
            return response()->json([
                'message' => 'Hanya informasi magang aktif yang dapat diakhiri'
            ], 400);
        }

        $apprenticeship->update([
            'status' => 'ended'
        ]);

        return response()->json([
            'message' => 'Informasi magang berhasil diakhiri'
        ]);
    }

    /**
     * ==========================
     * HELPER
     * ==========================
     */
    private function findApprenticeship(int $id, Request $request)
    {
        $facultyId = $request->admin_faculty_id;

        $apprenticeship = CareerInformation::where('id', $id)
            ->where('info_type', 'apprenticeship')
            ->where('faculty_id', $facultyId)
            ->first();

        if (!$apprenticeship) {
            abort(response()->json([
                'message' => 'Informasi magang tidak ditemukan atau bukan milik fakultas Anda'
            ], 404));
        }

        return $apprenticeship;
    }
}
