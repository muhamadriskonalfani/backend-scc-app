<?php

namespace App\Http\Controllers\Admin\JobVacancy;

use App\Http\Controllers\Controller;
use App\Jobs\SendNewCareerInformationEmail;
use App\Models\CareerInformation;
use App\Models\User;
use Illuminate\Http\Request;

class JobVacancyController extends Controller
{
    /**
     * List job vacancies (by faculty)
     */
    public function index(Request $request)
    {
        $facultyId = $request->admin_faculty_id;

        $query = CareerInformation::where('info_type', 'job_vacancy')
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

        $vacancies = $query
            ->with([
                'creator:id,name,email',
                'approver:id,name'
            ])
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $vacancies
        ]);
    }

    /**
     * Detail job vacancy
     */
    public function show($id, Request $request)
    {
        $facultyId = $request->admin_faculty_id;

        $vacancy = CareerInformation::with([
                'creator:id,name,email',
                'approver:id,name'
            ])
            ->where('id', $id)
            ->where('info_type', 'job_vacancy')
            ->where('faculty_id', $facultyId)
            ->first();

        if (!$vacancy) {
            return response()->json([
                'message' => 'Informasi loker tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $vacancy
        ]);
    }

    /**
     * Approve job vacancy
     */
    public function approve($id, Request $request)
    {
        $vacancy = $this->findVacancy($id, $request);

        // if ($vacancy->status === 'approved') {
        //     return response()->json([
        //         'message' => 'Informasi lowongan pekerjaan sudah aktif'
        //     ], 400);
        // }

        if ($vacancy->status !== 'pending') {
            return response()->json([
                'message' => 'Informasi lowongan pekerjaan sudah diproses'
            ], 400);
        }

        $vacancy->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        // NOTIFIKASI EMAIL 
        // Tahun lulus yang dianggap masih baru
        $currentYear = now()->year;
        $minimumGraduationYear = $currentYear - 1;

        // Cari alumni aktif yang baru lulus
        $alumni = User::where('role', 'alumni')
            ->where('status', 'active')
            ->whereHas('tracerStudy', function ($query) use ($minimumGraduationYear, $currentYear) {
                $query->whereBetween('graduation_year', [
                    $minimumGraduationYear,
                    $currentYear
                ]);
            })
            ->get();

        // Kirim email kepada alumni yang memenuhi kriteria
        foreach ($alumni as $user) {
            SendNewCareerInformationEmail::dispatch(
                $user,
                $vacancy
            );
        }

        return response()->json([
            'message' => 'Loker berhasil disetujui'
        ]);
    }

    /**
     * Reject job vacancy
     */
    public function reject($id, Request $request)
    {
        $vacancy = $this->findVacancy($id, $request);

        $vacancy->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Loker berhasil ditolak'
        ]);
    }

    /**
     * End job vacancy
     */
    public function end($id, Request $request)
    {
        $vacancy = $this->findVacancy($id, $request);

        if ($vacancy->status !== 'approved') {
            return response()->json([
                'message' => 'Hanya loker aktif yang dapat diakhiri'
            ], 400);
        }

        $vacancy->update([
            'status' => 'ended'
        ]);

        return response()->json([
            'message' => 'Loker berhasil diakhiri'
        ]);
    }

    /**
     * ==========================
     * HELPER
     * ==========================
     */
    private function findVacancy(int $id, Request $request)
    {
        $facultyId = $request->admin_faculty_id;

        $vacancy = CareerInformation::where('id', $id)
            ->where('info_type', 'job_vacancy')
            ->where('faculty_id', $facultyId)
            ->first();

        if (!$vacancy) {
            abort(response()->json([
                'message' => 'Loker tidak ditemukan atau bukan milik fakultas Anda'
            ], 404));
        }

        return $vacancy;
    }
}
