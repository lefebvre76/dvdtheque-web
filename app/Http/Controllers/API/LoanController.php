<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Box;
use App\Http\Resources\LoanResource;
use Illuminate\Support\Facades\Hash;
use App\Models\Loan;
use App\Http\Requests\PostLoan;
use App\Http\Requests\UpdateLoan;

class LoanController extends ApiController
{
    /**
     * @OA\Post(
     *     tags={"Loans"},
     *     path="/loans",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *          required=true,
     *          description="The loan informations",
     *          @OA\JsonContent(
     *              @OA\Property(property="box_id", type="integer", example="1"),
     *              @OA\Property(property="type", type="string", example="LOAN"),
     *              @OA\Property(property="contact", type="string", example="John Doo"),
     *              @OA\Property(property="contact_informations", type="string", example="json"),
     *              @OA\Property(property="reminder", type="string", example="timestamp"),
     *              @OA\Property(property="comment", type="string", example="comment"),
     *          )
     *     ),
     *      @OA\Response(
     *         response=201,
     *         description="Loan created",
     *         @OA\JsonContent(ref="#/components/schemas/Loan")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Product already loaned"),
     *     @OA\Response(response=422, description="Bad informations"),
     * )
     */
    public function store(PostLoan $request)
    {
        $loan_exists_query = Auth::user()->loans();
        if ($request->box_parent_id) {
            $loan_exists_query->where(function ($query) use ($request) {
                $query->where('box_id', $request->box_id)->where('box_parent_id', $request->box_parent_id);
            })->orWhere('box_id', $request->box_parent_id);
        } else {
            $loan_exists_query->where('box_id', $request->box_id)->whereNull('box_parent_id');
        }
        if ($loan_exists_query->exists()) {
            return $this->returnResponse('Product already loaned', 403);
        }
        $loan = Loan::create(array_merge([
            'user_id' => Auth::user()->id
        ], $request->only([
            'box_id', 'type', 'contact', 'contact_informations', 'reminder', 'comment'
        ])));
        return new LoanResource($loan);
    }

    /**
     * @OA\Put(
     *     tags={"Loans"},
     *     path="/loans/{id}",
     *     @OA\Parameter(in="path", name="id"),
     *     @OA\RequestBody(
     *          required=true,
     *          description="The loan informations",
     *          @OA\JsonContent(
     *              @OA\Property(property="box_id", type="integer", example="1"),
     *              @OA\Property(property="type", type="string", example="LOAN"),
     *              @OA\Property(property="contact", type="string", example="John Doo"),
     *              @OA\Property(property="contact_informations", type="string", example="json"),
     *              @OA\Property(property="reminder", type="string", example="timestamp"),
     *              @OA\Property(property="comment", type="string", example="comment"),
     *          )
     *     ),
     *     security={{"bearerAuth": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="Updated loan informations",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Product already loaned"),
     *     @OA\Response(response=422, description="Bad informations"),
     * )
     */
    public function update(UpdateLoan $request, Loan $loan)
    {
        if ($loan->user_id != Auth::user()->id) {
            return $this->returnNotFound();
        }

        $loan_exists_query = Auth::user()->loans()->where('id', '<>', $loan->id);
        if ($request->box_parent_id) {
            $loan_exists_query->where(function ($query) use ($request) {
                $query->where('box_id', $request->box_id)->where('box_parent_id', $request->box_parent_id);
            })->orWhere('box_id', $request->box_parent_id);
        } else {
            $loan_exists_query->where('box_id', $request->box_id)->whereNull('box_parent_id');
        }
        if ($loan_exists_query->exists()) {
            return $this->returnResponse('Product already loaned', 403);
        }
        $loan->update($request->only([
            'box_id', 'type', 'contact', 'contact_informations', 'reminder', 'comment'
        ]));
        return $this->returnSuccess(new LoanResource($loan));
    }

    /**
     * @OA\Delete(
     *     tags={"Loans"},
     *     path="/loans/{id}",
     *     @OA\Parameter(in="path", name="id"),
     *     security={{"bearerAuth": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="Updated loan informations",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=204, description="Loan removed"),
     *     @OA\Response(response=404, description="Loan not found"),
     * )
     */
    public function delete(Loan $loan)
    {
        if ($loan->user_id != Auth::user()->id) {
            return $this->returnNotFound();
        }
        $loan->delete();
        return $this->returnNoContent();
    }
}