<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @OA\Schema(
     *      schema="User",
     *      title="User",
     *      @OA\Property(property="id", type="integer"),
     *      @OA\Property(property="name", type="string"),
     *      @OA\Property(property="email", type="string"),
     *      @OA\Property(property="total_boxes", type="integer"),
     *      @OA\Property(property="total_movies", type="integer"),
     * )
    */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'total_boxes' => $this->boxes()->wherePivot('wishlist', false)->count(),
            'total_movies' => $this->movies()->count(),
            'favorite_kinds' => PopularItemResource::collection($this->favoriteKinds(3))
        ];
    }
}
