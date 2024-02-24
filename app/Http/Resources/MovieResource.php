<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class MovieResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @OA\Schema(
     *      schema="Movie",
     *      title="Movie",
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
     *      @OA\Property(property="parent_boxes", type="array", @OA\Items(ref="#/components/schemas/LightBox")),
     *      @OA\Property(property="loans", type="array", @OA\Items(ref="#/components/schemas/Loan")),
     * )
    */
    public function toArray(Request $request): array
    {
        if (Auth::user()) {
            $parents = $this->parentBoxes()->whereIn('box_box.pack_id', Auth::user()->boxes->pluck('id')->toArray())->get();
            $loans = Auth::user()->loans()->where('box_id', $this->id)->get();
        } else {
            $parents = $this->parentBoxes;
            $loans = collect([]);
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
            'parent_boxes' => LightBoxResource::collection($parents),
            'loans' => LoanResource::collection($loans)
        ];
    }
}
