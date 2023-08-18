<?php

namespace Layerok\Restapi\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Layerok\PosterPos\Models\City;

class CityController extends Controller
{
    public function fetch(): JsonResponse
    {
        $offset = input('offset');
        $limit = input('limit');
        $includeSpots = input('includeSpots');
        $includeDistricts = input('includeDistricts');

        $query = City::query();

        if($includeSpots) {
            $query->with(['spots.photos', 'districts']);
        }

        if($includeDistricts) {
            $query->with(['districts.spots']);
        }

        if($limit) {
            $query->limit($limit);
        }

        if($offset) {
            $query->offset($offset);
        }

        $records = $query->get();

        return response()->json([
            'data' => $records->toArray(),
            'meta' => [
                'total' => $records->count(),
                'offset' => $offset,
                'limit' => $limit
            ]
        ]);
    }

    public function one(): JsonResponse {
        $slug_or_id = input('slug_or_id');

        $record = City::findBySlugOrId($slug_or_id);

        if(!$record) {
           return response()->json(['error' => 'Not Found!'], 404);
        }

        return response()->json($record);
    }

    public function main(): JsonResponse {
        $city = City::with(['spots', 'districts.spots'])->where('is_main', 1)->first();
        if($city) {
            return response()->json($city);
        }
        return response()->json(['error' => 'Not Found!'], 404);
    }
}
