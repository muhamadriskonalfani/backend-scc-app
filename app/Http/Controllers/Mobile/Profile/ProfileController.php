<?php

namespace App\Http\Controllers\Mobile\Profile;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

class ProfileController extends Controller
{
    /**
     * Tampilkan profile user login
     */
    public function show(Request $request)
    {
        try {
            $profile = Profile::with([
                    'user:id,name,email,role',
                    'user.tracerStudy:id,user_id,student_id_number,faculty_id,study_program_id,entry_year,employment_status,employment_type,current_workplace,job_title,job_category,suggestion_for_university',
                    'user.tracerStudy.faculty:id,name',
                    'user.tracerStudy.studyProgram:id,name',
                ])
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'profile' => null
                ]);
            }

            return response()->json([
                'success' => true,
                'profile' => [
                    'name'          => $profile->user->name,
                    'email'         => $profile->user->email,
                    'role'          => $profile->user->role,

                    'gender'        => $profile->gender,
                    'image'         => $profile->image,
                    'phone'         => $profile->phone,
                    'domicile'      => $profile->domicile,
                    'testimonial'   => $profile->testimonial,
                    'bio'           => $profile->bio,
                    'education'     => $profile->education,
                    'skills'        => $profile->skills,
                    'experience'    => $profile->experience,
                    'linkedin_url'  => $profile->linkedin_url,
                    'cv_file'       => $profile->cv_file,
                    'alumni_tag' => $profile->alumni_tag,

                    'nim'           => $profile->user->tracerStudy?->student_id_number,
                    'faculty'       => $profile->user->tracerStudy?->faculty?->name,
                    'study_program' => $profile->user->tracerStudy?->studyProgram?->name,
                    'entry_year'    => $profile->user->tracerStudy?->entry_year,
                    'employment_status' => $profile->user->tracerStudy?->employment_status,
                    'employment_type' => $profile->user->tracerStudy?->employment_type,
                    'current_workplace' => $profile->user->tracerStudy?->current_workplace,
                    'job_title' => $profile->user->tracerStudy?->job_title,
                    'job_category' => $profile->user->tracerStudy?->job_category,
                    'suggestion_for_university' => $profile->user->tracerStudy?->suggestion_for_university,
                ]
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exception: Gagal mengambil data profile'
            ], 500);
        }
    }

    /**
     * Simpan profile pertama kali
     */
    public function store(Request $request)
    {
        // Cegah duplicate profile
        if (Profile::where('user_id', $request->user()->id)->exists()) {
            return response()->json([
                'message' => 'Profile sudah ada'
            ], 409);
        }

        $validated = $request->validate([
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'alumni_tag' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'phone' => 'nullable|string|max:20',
            'domicile' => 'nullable|string',
            'testimonial' => 'nullable|string',
            'bio' => 'nullable|string',
            'education' => 'nullable|string',
            'skills' => 'nullable|string',
            'experience' => 'nullable|string',
            'linkedin_url' => 'nullable|url',
            'cv_file' => 'nullable|mimes:pdf|max:5120',
        ]);

        DB::beginTransaction();

        try {
            // Upload image jika ada
            if ($request->hasFile('image')) {
                $filename = 'profile_' . Str::random(20) . '.webp';
                $path = 'assets/profiles/images';

                $image = ImageManager::imagick()
                    ->read($request->file('image')->getPathname())
                    ->cover(600, 600)
                    ->toWebp(85);

                Storage::disk('public')->put(
                    $path . '/' . $filename,
                    (string) $image
                );

                $validated['image'] = $path . '/' . $filename;
            }
            
            // Upload image (tanda alumni) jika ada
            if ($request->hasFile('alumni_tag')) {
                $filename = 'alumni_tags_' . Str::random(20) . '.webp';
                $path = 'assets/profiles/alumni_tags';

                $image = ImageManager::imagick()
                    ->read($request->file('alumni_tag')->getPathname())
                    ->cover(600, 600)
                    ->toWebp(85);

                Storage::disk('public')->put(
                    $path . '/' . $filename,
                    (string) $image
                );

                $validated['alumni_tag'] = $path . '/' . $filename;
            }

            // Upload CV
            if ($request->hasFile('cv_file')) {
                $cvFile = $request->file('cv_file');
                $cvName = 'cv_' . uniqid() . '.' . $cvFile->getClientOriginalExtension();
                $cvLocation = 'assets/profiles/cv';

                $validated['cv_file'] = $cvFile->storeAs($cvLocation, $cvName, 'public');
            }

            $validated['user_id'] = $request->user()->id;

            $profile = Profile::create($validated);

            DB::commit();

            return response()->json([
                'message' => 'Profile berhasil dibuat',
                'profile' => $profile
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Profile gagal dibuat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update profile
     */
    public function update(Request $request)
    {
        $profile = Profile::where('user_id', $request->user()->id)->first();

        if (!$profile) {
            return response()->json([
                'message' => 'Profile belum dibuat'
            ], 404);
        }

        $validated = $request->validate([
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'alumni_tag' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'phone' => 'nullable|string|max:20',
            'domicile' => 'nullable|string',
            'testimonial' => 'nullable|string',
            'bio' => 'nullable|string',
            'education' => 'nullable|string',
            'skills' => 'nullable|string',
            'experience' => 'nullable|string',
            'linkedin_url' => 'nullable|url',
            'cv_file' => 'nullable|mimes:pdf|max:5120',
        ]);

        DB::beginTransaction();

        try {
            // Update image dengan Imagick + WebP
            if ($request->hasFile('image')) {
                if ($profile->image) {
                    Storage::disk('public')->delete($profile->image);
                }

                $filename = 'profile_' . Str::random(20) . '.webp';
                $path = 'assets/profiles/images';

                $image = ImageManager::imagick()
                    ->read($request->file('image')->getPathname())
                    ->cover(600, 600)
                    ->toWebp(85);

                Storage::disk('public')->put(
                    $path . '/' . $filename,
                    (string) $image
                );

                $validated['image'] = $path . '/' . $filename;
            }
            
            // Update image (tanda alumni) dengan Imagick + WebP
            if ($request->hasFile('alumni_tag')) {
                if ($profile->alumni_tag) {
                    Storage::disk('public')->delete($profile->alumni_tag);
                }

                $filename = 'alumni_tags_' . Str::random(20) . '.webp';
                $path = 'assets/profiles/alumni_tags';

                $image = ImageManager::imagick()
                    ->read($request->file('alumni_tag')->getPathname())
                    ->cover(600, 600)
                    ->toWebp(85);

                Storage::disk('public')->put(
                    $path . '/' . $filename,
                    (string) $image
                );

                $validated['alumni_tag'] = $path . '/' . $filename;
            }

            // Update CV
            if ($request->hasFile('cv_file')) {
                if ($profile->cv_file) {
                    Storage::disk('public')->delete($profile->cv_file);
                }

                $cvFile = $request->file('cv_file');
                $cvName = 'cv_' . uniqid() . '.' . $cvFile->getClientOriginalExtension();
                $cvLocation = 'assets/profiles/cv';

                $validated['cv_file'] = $cvFile->storeAs($cvLocation, $cvName, 'public');
            }

            $profile->update($validated);

            DB::commit();

            return response()->json([
                'message' => 'Profile berhasil diperbarui',
                'profile' => $profile
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Profile gagal diperbarui',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
