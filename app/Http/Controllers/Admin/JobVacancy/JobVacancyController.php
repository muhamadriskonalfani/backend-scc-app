<?php

namespace App\Http\Controllers\Admin\JobVacancy;

use App\Http\Controllers\Controller;
use App\Jobs\SendNewCareerInformationEmail;
use App\Models\CareerInformation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

class JobVacancyController extends Controller
{
    /**
     * List job vacancies (by faculty)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = CareerInformation::where('info_type', 'job_vacancy');

        // Admin hanya melihat fakultasnya
        if ($user->role === 'admin') {
            $facultyId = $user->adminProfile?->faculty_id;

            if (!$facultyId) {
                return response()->json([
                    'message' => 'Admin profile atau fakultas tidak ditemukan'
                ], 403);
            }

            $query->where('faculty_id', $facultyId);
        }

        // super_admin tidak diberi filter faculty_id
        elseif ($user->role !== 'super_admin') {
            return response()->json([
                'message' => 'Unauthorized role'
            ], 403);
        }

        // Filter
        if ($request->filled('search')) {
            $search = $request->search;

            $query->whereHas('creator', function ($userQuery) use ($search) {
                $userQuery->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('company_name')) {
            $query->where('company_name', 'like', '%' . $request->company_name . '%');
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date,
                $request->to_date
            ]);
        }

        $jobvacancies = $query
            ->with([
                'creator:id,name,email',
                'approver:id,name'
            ])
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $jobvacancies
        ]);
    }

    /**
     * Detail job vacancy
    */
    public function show($id, Request $request)
    {
        $user = $request->user();

        $query = CareerInformation::with([
            'creator:id,name,email',
            'approver:id,name'
        ])
            ->where('id', $id)
            ->where('info_type', 'job_vacancy');

        if ($user->role === 'admin') {
            $facultyId = $user->adminProfile?->faculty_id;

            if (!$facultyId) {
                return response()->json([
                    'message' => 'Admin profile atau fakultas tidak ditemukan'
                ], 403);
            }

            $query->where('faculty_id', $facultyId);
        } elseif ($user->role !== 'super_admin') {
            return response()->json([
                'message' => 'Unauthorized role'
            ], 403);
        }

        $jobvacancies = $query->first();

        if (!$jobvacancies) {
            return response()->json([
                'message' => 'Informasi lowongan kerja tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $jobvacancies
        ]);
    }

    /**
     * Admin / Super Admin: create jobvacancy
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized role'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'company_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'expired_at' => 'nullable|date|after:today',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'application_link' => 'required|url|max:255',
            'faculty_id' => $user->role === 'super_admin'
                ? 'required|exists:faculties,id'
                : 'nullable|exists:faculties,id',
        ]);

        /*
        * Admin hanya boleh membuat informasi
        * untuk fakultasnya sendiri.
        *
        * Super admin bebas memilih fakultas.
        */
        if ($user->role === 'admin') {
            $facultyId = $user->adminProfile?->faculty_id;

            if (!$facultyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin profile atau fakultas tidak ditemukan'
                ], 403);
            }

            // Abaikan faculty_id dari request untuk admin
            $facultyId = $facultyId;
        } else {
            $facultyId = $validated['faculty_id'];
        }

        DB::beginTransaction();

        try {
            $imagePath = null;

            // Upload image jika ada
            if ($request->hasFile('image')) {
                $filename = 'jobvacancy_' . Str::random(20) . '.webp';
                $path = 'assets/job_vacancies';

                $image = ImageManager::imagick()
                    ->read($request->file('image')->getPathname())
                    ->scaleDown(width: 1200)
                    ->toWebp(85);

                Storage::disk('public')->put(
                    $path . '/' . $filename,
                    (string) $image
                );

                $imagePath = $path . '/' . $filename;
            }

            $data = CareerInformation::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'company_name' => $validated['company_name'],
                'location' => $validated['location'],
                'expired_at' => $validated['expired_at'] ?? null,
                'application_link' => $validated['application_link'],
                'image' => $imagePath,

                'info_type' => 'job_vacancy',

                // Dibuat langsung aktif/approved oleh admin
                'status' => 'approved',

                'created_by' => $user->id,
                'faculty_id' => $facultyId,

                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Informasi lowongan kerja berhasil ditambahkan',
                'data' => $data
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan informasi lowongan kerja',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin / Super Admin: update jobvacancy
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized role'
            ], 403);
        }

        // Memastikan data berada dalam cakupan akses user
        $data = $this->findVacancy($id, $request);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'company_name' => 'sometimes|required|string|max:255',
            'location' => 'sometimes|required|string|max:255',
            'expired_at' => 'nullable|date|after:today',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'application_link' => 'sometimes|required|url|max:255',
            'faculty_id' => 'sometimes|required|exists:faculties,id',
        ]);

        /*
        * Admin tidak boleh memindahkan data
        * ke fakultas lain.
        *
        * Super admin boleh mengubah fakultas.
        */
        if ($user->role === 'admin') {
            unset($validated['faculty_id']);
        }

        DB::beginTransaction();

        try {
            // Upload image baru jika ada
            if ($request->hasFile('image')) {

                // Hapus image lama
                if (
                    $data->image &&
                    Storage::disk('public')->exists($data->image)
                ) {
                    Storage::disk('public')->delete($data->image);
                }

                $filename = 'jobvacancy_' . Str::random(20) . '.webp';
                $path = 'assets/job_vacancies';

                $image = ImageManager::imagick()
                    ->read($request->file('image')->getPathname())
                    ->scaleDown(width: 1200)
                    ->toWebp(85);

                Storage::disk('public')->put(
                    $path . '/' . $filename,
                    (string) $image
                );

                $validated['image'] = $path . '/' . $filename;
            }

            /*
            * Jika admin/super_admin mengubah data,
            * tetap dianggap approved.
            */
            $validated['status'] = 'approved';

            $data->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Informasi lowongan kerja berhasil diperbarui',
                'data' => $data->fresh()
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui informasi lowongan kerja',
                'error' => $e->getMessage()
            ], 500);
        }
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
        $user = $request->user();

        $query = CareerInformation::where('id', $id)
            ->where('info_type', 'job_vacancy');

        if ($user->role === 'admin') {
            $facultyId = $user->adminProfile?->faculty_id;

            if (!$facultyId) {
                abort(response()->json([
                    'message' => 'Admin profile atau fakultas tidak ditemukan'
                ], 403));
            }

            $query->where('faculty_id', $facultyId);
        } elseif ($user->role !== 'super_admin') {
            abort(response()->json([
                'message' => 'Unauthorized role'
            ], 403));
        }

        $jobvacancies = $query->first();

        if (!$jobvacancies) {
            abort(response()->json([
                'message' => 'Informasi lowongan kerja tidak ditemukan'
            ], 404));
        }

        return $jobvacancies;
    }
}
