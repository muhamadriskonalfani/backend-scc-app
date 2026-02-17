<?php

namespace App\Http\Controllers\Mobile\JobVacancy;

use App\Http\Controllers\Controller;
use App\Models\CareerInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

class JobVacancyController extends Controller
{
    /**
     * List informasi lowongan kerja 
     */
    public function index()
    {
        $data = CareerInformation::query()
            ->where('info_type', 'job_vacancy')
            ->where('status', 'approved')
            ->latest()
            ->select([
                'id',
                'title',
                'company_name',
                'location',
                'image',
                'created_at',
                'application_link',
            ])
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => $data->total() > 0
                ? 'Data lowongan kerja berhasil diambil'
                : 'Belum ada informasi lowongan kerja',
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
            ],
        ]);
    }

    /**
     * Detail informasi lowongan kerja
     */
    public function show($id)
    {
        $data = CareerInformation::with('creator:id,name')
            ->where('id', $id)
            ->where('info_type', 'job_vacancy')
            // ->where('status', 'approved')
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
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'application_link' => 'required|url|max:255',
        ]);

        $user = Auth::user();

        if (!$user->tracerStudy) {
            return response()->json([
                'success' => false,
                'message' => 'Data tracer study belum lengkap'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $imagePath = null;

            // Upload image jika ada
            if ($request->hasFile('image')) {
                $filename = 'job_vacancy_' . Str::random(20) . '.webp';
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
                ...$validated,
                'image' => $imagePath,
                'info_type' => 'job_vacancy',
                'status' => 'pending',
                'created_by' => $user->id,
                'faculty_id' => $user->tracerStudy->faculty_id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Informasi lowongan kerja berhasil dibuat dan menunggu persetujuan admin',
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
     * Alumni: update info lowongan kerja
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
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'application_link' => 'sometimes|required|url|max:255',
        ]);

        DB::beginTransaction();

        try {
            // Jika upload image baru
            if ($request->hasFile('image')) {

                // hapus image lama
                if ($data->image && Storage::disk('public')->exists($data->image)) {
                    Storage::disk('public')->delete($data->image);
                }

                $filename = 'job_vacancy_' . Str::random(20) . '.webp';
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

            $data->update([
                ...$validated,
                'status' => 'pending',
                'approved_by' => null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Informasi lowongan kerja berhasil diperbarui dan menunggu persetujuan admin',
                'data' => $data
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data',
                'error' => $e->getMessage()
            ], 500);
        }
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
            'message' => $data->total()
                ? 'Data lowongan Anda berhasil diambil'
                : 'Anda belum memiliki lowongan',
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
            ],
        ]);
    }
}
