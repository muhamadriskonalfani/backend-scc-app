<?php

namespace App\Http\Controllers\Mobile\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CampusInformationController extends Controller
{
    /**
     * List informasi kampus (global + fakultas user)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $facultyId = optional($user->tracerStudy)->faculty_id;

        $information = CampusInformation::query()
            ->where('status', 'active')
            ->where(function ($query) use ($facultyId) {
                $query->whereNull('faculty_id');

                if ($facultyId) {
                    $query->orWhere('faculty_id', $facultyId);
                }
            })
            ->latest()
            ->paginate(10);

        return response()->json([
            'data' => $information->getCollection()->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'image' => $item->image,
                    'excerpt' => Str::limit(strip_tags($item->description), 120),
                    'created_at' => $item->created_at->format('Y-m-d'),
                ];
            }),
            'meta' => [
                'current_page' => $information->currentPage(),
                'last_page' => $information->lastPage(),
                'per_page' => $information->perPage(),
                'total' => $information->total(),
            ],
        ]);
    }

    /**
     * Detail informasi kampus
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $facultyId = optional($user->tracerStudy)->faculty_id;

        $information = CampusInformation::query()
            ->where('status', 'active')
            ->where('id', $id)
            ->where(function ($query) use ($facultyId) {
                $query->whereNull('faculty_id');

                if ($facultyId) {
                    $query->orWhere('faculty_id', $facultyId);
                }
            })
            ->first();

        if (!$information) {
            return response()->json([
                'message' => 'Informasi kampus tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'id' => $information->id,
            'title' => $information->title,
            'image' => $information->image,
            'description' => $information->description,
            'created_at' => $information->created_at->format('Y-m-d'),
        ]);
    }
}
