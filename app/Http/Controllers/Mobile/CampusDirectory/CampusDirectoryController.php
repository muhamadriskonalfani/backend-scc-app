<?php

namespace App\Http\Controllers\Mobile\CampusDirectory;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CampusDirectoryController extends Controller
{
    /**
     * List Direktori Kampus (filterable)
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->whereIn('role', ['student', 'alumni'])
            ->where('status', 'active')
            ->with([
                'profile:id,user_id,image',
                'tracerStudy:id,user_id,faculty_id,study_program_id,entry_year',
                'tracerStudy.faculty:id,name',
                'tracerStudy.studyProgram:id,name',
            ]);

        // 🔎 Filter: Tipe
        if ($request->filled('type')) {
            $query->where('role', $request->type);
        }

        // 🔎 Filter: Fakultas
        if ($request->filled('faculty_id')) {
            $query->whereHas('tracerStudy', function ($q) use ($request) {
                $q->where('faculty_id', $request->faculty_id);
            });
        }

        // 🔎 Filter: Prodi
        if ($request->filled('study_program_id')) {
            $query->whereHas('tracerStudy', function ($q) use ($request) {
                $q->where('study_program_id', $request->study_program_id);
            });
        }

        // 🔎 Filter: Angkatan
        if ($request->filled('entry_year')) {
            $query->whereHas('tracerStudy', function ($q) use ($request) {
                $q->where('entry_year', $request->entry_year);
            });
        }

        $users = $query
            ->select('id', 'name', 'role')
            ->paginate(10);

        // 🧼 Sanitasi response
        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'photo' => $user->profile->image ?? null,
                'faculty' => $user->tracerStudy->faculty->name ?? null,
                'study_program' => $user->tracerStudy->studyProgram->name ?? null,
                'entry_year' => $user->tracerStudy->entry_year ?? null,
                'status' => $user->role,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Detail Profil Publik
     */
    public function show($id)
    {
        $user = User::where('id', $id)
            ->whereIn('role', ['student', 'alumni'])
            ->where('status', 'active')
            ->with([
                'profile:id,user_id,image,bio,skills,experience,testimonial',
                'tracerStudy.faculty:id,name',
                'tracerStudy.studyProgram:id,name',
                'tracerStudy:id,user_id,entry_year,graduation_year,current_workplace,job_title',
            ])
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $user->name,
                'photo' => $user->profile->image ?? null,
                'status' => $user->role,
                'faculty' => $user->tracerStudy->faculty->name ?? null,
                'study_program' => $user->tracerStudy->studyProgram->name ?? null,
                'entry_year' => $user->tracerStudy->entry_year ?? null,
                'graduation_year' => $user->tracerStudy->graduation_year ?? null,
                'job_title' => $user->tracerStudy->job_title ?? null,
                'current_workplace' => $user->tracerStudy->current_workplace ?? null,
                'bio' => $user->profile->bio ?? null,
                'skills' => $user->profile->skills ?? null,
                'testimonial' => $user->profile->testimonial ?? null,
            ]
        ]);
    }
}
