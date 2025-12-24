<?php

namespace App\Http\Controllers\Mobile\Profile;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Tampilkan profile user login
     */
    public function show(Request $request)
    {
        $profile = Profile::where('user_id', $request->user()->id)->first();

        if (!$profile) {
            return response()->json([
                'exists' => false,
                'profile' => null
            ]);
        }

        return response()->json([
            'exists' => true,
            'profile' => $profile
        ]);
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
            'phone' => 'nullable|string|max:20',
            'testimonial' => 'nullable|string',
            'bio' => 'nullable|string',
            'education' => 'nullable|string',
            'skills' => 'nullable|string',
            'experience' => 'nullable|string',
            'linkedin_url' => 'nullable|url',
            'cv_file' => 'nullable|mimes:pdf|max:5120',
        ]);

        // Upload image
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('profiles/images', 'public');
        }

        // Upload CV
        if ($request->hasFile('cv_file')) {
            $validated['cv_file'] = $request->file('cv_file')->store('profiles/cv', 'public');
        }

        $validated['user_id'] = $request->user()->id;

        $profile = Profile::create($validated);

        return response()->json([
            'message' => 'Profile berhasil dibuat',
            'profile' => $profile
        ], 201);
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
            'phone' => 'nullable|string|max:20',
            'testimonial' => 'nullable|string',
            'bio' => 'nullable|string',
            'education' => 'nullable|string',
            'skills' => 'nullable|string',
            'experience' => 'nullable|string',
            'linkedin_url' => 'nullable|url',
            'cv_file' => 'nullable|mimes:pdf|max:5120',
        ]);

        // Update image
        if ($request->hasFile('image')) {
            if ($profile->image) {
                Storage::disk('public')->delete($profile->image);
            }
            $validated['image'] = $request->file('image')->store('profiles/images', 'public');
        }

        // Update CV
        if ($request->hasFile('cv_file')) {
            if ($profile->cv_file) {
                Storage::disk('public')->delete($profile->cv_file);
            }
            $validated['cv_file'] = $request->file('cv_file')->store('profiles/cv', 'public');
        }

        $profile->update($validated);

        return response()->json([
            'message' => 'Profile berhasil diperbarui',
            'profile' => $profile
        ]);
    }
}
