<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BoxResource extends JsonResource
{
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
     *      @OA\Property(property="boxes", type="array", @OA\Items(type="object", description="Box Object")),
     * )
    */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'original_title' => $this->original_title,
            'year' => $this->year,
            'synopsis' => $this->synopsis,
            'edition' => $this->edition,
            'editor' => $this->editor,
            'illustration' => $this->getMedia('cover')->first() ? new IllustrationResource($this->getMedia('cover')->first()) : null,
            'boxes' => BoxResource::collection($this->boxes),
        ];
    }
}
