<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PopularItemResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @OA\Schema(
     *      schema="PopularItem",
     *      title="PopularItem",
     *      @OA\Property(property="id", type="integer"),
     *      @OA\Property(property="name", type="string"),
     *      @OA\Property(property="total", type="integer"),
     * )
    */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'total' => $this->total,
        ];
    }
}
