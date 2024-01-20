<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Loan;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UpdateLoan extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $box_id_rules = ['required'];
        $box_parent_id_rules = ['nullable'];
        if ($this->type && $this->type == Loan::TYPE_LOAN) {
            $box_ids = Auth::user()->boxes()->wherePivot('wishlist', false)->get()->pluck('id')->toArray();
            if ($this->box_parent_id) {
                $box_parent_ids = Auth::user()->boxes()->wherePivot('wishlist', false)->get()->pluck('id')->toArray();
                $box_parent_id_rules[] = Rule::in($box_parent_ids);
                $parent_box = Auth::user()->boxes()->wherePivot('wishlist', false)->where('boxes.id', $this->box_parent_id)->first();
                if ($parent_box) {
                    $box_ids = $parent_box->boxes->pluck('id')->toArray();
                }
            }
            $box_id_rules[] = Rule::in($box_ids);
        }
        return [
            'box_id' => $box_id_rules,
            'box_parent_id' => $box_parent_id_rules,
            'type' => ['required', Rule::in([Loan::TYPE_LOAN, Loan::TYPE_BORROW])],
            'contact' => 'required',
            'contact_informations' => 'nullable|array',
            'reminder' => 'nullable|integer',
            'comment' => 'nullable|string',
        ];
    }
}
