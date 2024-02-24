<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class LoanResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @OA\Schema(
     *      schema="Loan",
     *      title="Loan",
     *      @OA\Property(property="id", type="integer"),
     *      @OA\Property(property="type", type="string"),
     *      @OA\Property(property="box", ref="#/components/schemas/LightBox"),
     *      @OA\Property(property="parent_box", ref="#/components/schemas/LightBox"),
     *      @OA\Property(property="contact", type="string"),
     *      @OA\Property(property="contact_informations", type="string"),
     *      @OA\Property(property="reminder", type="integer"),
     *      @OA\Property(property="comment", type="string"),
     * )
    */
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'box' => new LightBoxResource($this->box),
            'parent_box' => $this->parentBox ? new LightBoxResource($this->parentBox) : null,
            'type' => $this->type,
            'contact' => $this->contact,
            'contact_informations' => $this->contact_informations,
            'reminder' => $this->reminder ? $this->reminder->timestamp : null,
            'comment' => $this->comment,
        ];
    }
}
