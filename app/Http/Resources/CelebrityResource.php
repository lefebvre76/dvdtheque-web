<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CelebrityResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @OA\Schema(
     *      schema="Celebrity",
     *      title="Celebrity",
     *      @OA\Property(property="id", type="integer"),
     *      @OA\Property(property="name", type="string"),
     * )
    */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
