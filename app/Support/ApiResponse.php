<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiResponse
{
    /**
     * @param class-string<JsonResource> $resource
     */
    public static function collection(LengthAwarePaginator $paginator, string $resource): JsonResponse
    {
        return response()->json([
            'data' => collect($paginator->items())
                ->map(fn ($item) => (new $resource($item))->resolve(request()))
                ->all(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public static function item(JsonResource $resource, int $status = 200): JsonResponse
    {
        return response()->json(['data' => $resource->resolve(request())], $status);
    }
}
