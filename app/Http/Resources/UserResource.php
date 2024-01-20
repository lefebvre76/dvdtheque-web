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
     *      @OA\Property(property="favorite_kinds", type="array", @OA\Items(ref="#/components/schemas/PopularItem")),
     *      @OA\Property(property="favorite_directors", type="array", @OA\Items(ref="#/components/schemas/PopularItem")),
     *      @OA\Property(property="favorite_actors", type="array", @OA\Items(ref="#/components/schemas/PopularItem")),
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
            'favorite_kinds' => PopularItemResource::collection($this->favoriteKinds(3)),
            'favorite_directors' => CelebrityResource::collection($this->favoriteCelebrities(['Réalisateur'], 3)),
            'favorite_actors' => CelebrityResource::collection($this->favoriteCelebrities(['Voix en VO', 'Voix en VF', 'Acteur'], 3))
        ];
    }
}
