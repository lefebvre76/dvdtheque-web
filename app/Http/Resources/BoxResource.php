<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class BoxResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @OA\Schema(
     *      schema="Box",
     *      title="Box",
     *      @OA\Property(property="id", type="integer"),
     *      @OA\Property(property="type", type="string"),
     *      @OA\Property(property="title", type="string"),
     *      @OA\Property(property="original_title", type="string"),
     *      @OA\Property(property="year", type="integer"),
     *      @OA\Property(property="synopsis", type="string"),
     *      @OA\Property(property="edition", type="string"),
     *      @OA\Property(property="editor", type="string"),
     *      @OA\Property(property="illustration", ref="#/components/schemas/Illustration"),
     *      @OA\Property(property="kinds", type="array", @OA\Items(ref="#/components/schemas/Kind")),
     *      @OA\Property(property="directors", type="array", @OA\Items(ref="#/components/schemas/Celebrity")),
     *      @OA\Property(property="actors", type="array", @OA\Items(ref="#/components/schemas/Celebrity")),
     *      @OA\Property(property="composers", type="array", @OA\Items(ref="#/components/schemas/Celebrity")),
     *      @OA\Property(property="boxes", type="array", @OA\Items(ref="#/components/schemas/LightBox")),
     *      @OA\Property(property="in_wishlist", type="boolean"),
     *      @OA\Property(property="in_collection", type="boolean"),
     * )
    */
    public function toArray(Request $request): array
    {
        $in_wishlist = false;
        $in_collection = false;
        $box = Auth::user()->boxes()->where('boxes.id', $this->id)->first();
        if ($box) {
            $in_wishlist = filter_var($box->pivot->wishlist, FILTER_VALIDATE_BOOLEAN);
            $in_collection = !filter_var($box->pivot->wishlist, FILTER_VALIDATE_BOOLEAN);
        }
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'original_title' => $this->original_title,
            'year' => intval($this->year),
            'synopsis' => $this->synopsis,
            'edition' => $this->edition,
            'editor' => $this->editor,
            'illustration' => $this->getMedia('cover')->first() ? new IllustrationResource($this->getMedia('cover')->first()) : null,
            'kinds' => KindResource::collection($this->kinds),
            'directors' => CelebrityResource::collection($this->directors),
            'actors' => CelebrityResource::collection($this->actors),
            'composers' => CelebrityResource::collection($this->composers),
            'boxes' => LightBoxResource::collection($this->boxes),
            'in_wishlist' => $in_wishlist,
            'in_collection' => $in_collection
        ];
    }
}
