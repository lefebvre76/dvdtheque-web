<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IllustrationResource extends JsonResource
{
    /**
     * @OA\Schema(
     *      schema="Illustration",
     *      title="Illustration",
     *      @OA\Property(property="original", type="string"),
     *      @OA\Property(property="thumbnail", type="string")
     * )
    */
    public function toArray(Request $request): array
    {
        return [
            'original' => $this->getUrl(),
            'thumbnail' => $this->getUrl('thumbnail')
        ];
    }
}
