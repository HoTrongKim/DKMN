<?php

namespace App\Http\Controllers;

use App\Models\Tram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TramController extends Controller
{
    public function getData(Request $request)
    {
        $query = Tram::query()->with('tinhThanh');
        $cacheable = !$request->filled('keyword');
        $cacheKey = null;

        if ($request->filled('tinh_thanh_id')) {
            $query->where('tinh_thanh_id', (int) $request->input('tinh_thanh_id'));
            $cacheKey = $cacheKey ?? 'tram_data';
            $cacheKey .= ':city_' . (int) $request->input('tinh_thanh_id');
        }

        if ($request->filled('loai')) {
            $query->where('loai', $request->input('loai'));
            $cacheKey = $cacheKey ?? 'tram_data';
            $cacheKey .= ':type_' . $request->input('loai');
        }

        if ($request->filled('keyword')) {
            $keyword = trim($request->input('keyword'));
            $query->where('ten', 'like', "%{$keyword}%");
        }

        $trams = $query->orderBy('ten')->get()->map(function (Tram $tram) {
            return [
                'id' => $tram->id,
                'ten' => $tram->ten,
                'loai' => $tram->loai,
                'dia_chi' => $tram->dia_chi,
                'tinh_thanh_id' => $tram->tinh_thanh_id,
                'tinh_thanh' => $tram->tinhThanh->ten ?? null,
            ];
        });

        if ($cacheable) {
            $cacheKey = $cacheKey ?? 'tram_data:all';
            $trams = Cache::remember($cacheKey, 300, fn () => $resolver());
        } else {
            $trams = $resolver();
        }

        return response()->json(['data' => $trams]);
    }
}
