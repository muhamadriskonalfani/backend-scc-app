<?php

namespace App\Http\Controllers\Mobile\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CampusInformationController extends Controller
{
    /**
     * List informasi kampus (pagination)
     */
    public function index(Request $request)
    {
        $information = CampusInformation::query()
            ->where('status', 'active')
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
                'total' => $information->total(),
            ],
        ]);
    }

    /**
     * Detail informasi kampus
     */
    public function show($id)
    {
        $information = CampusInformation::where('status', 'active')
            ->where('id', $id)
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
