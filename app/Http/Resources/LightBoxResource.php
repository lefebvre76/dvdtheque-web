<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use App\Models\Loan;

class LightBoxResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @OA\Schema(
     *      schema="LightBox",
     *      title="LightBox",
     *      @OA\Property(property="id", type="integer"),
     *      @OA\Property(property="type", type="string"),
     *      @OA\Property(property="title", type="string"),
     *      @OA\Property(property="illustration", ref="#/components/schemas/Illustration"),
     *      @OA\Property(property="loaned", type="Bool"),
     *      @OA\Property(property="borrowed", type="Bool"),
     * )
    */
    public function toArray(Request $request): array
    {
        $loaned = Auth::user()->loans()->where('box_id', $this->id)->where('type', Loan::TYPE_LOAN)->exists();
        $borrowed = Auth::user()->loans()->where('box_id', $this->id)->where('type', Loan::TYPE_BORROW)->exists();
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'illustration' => $this->getMedia('cover')->first() ? new IllustrationResource($this->getMedia('cover')->first()) : null,
            'loaned' => $loaned,
            'borrowed' => $borrowed
        ];
    }
}
