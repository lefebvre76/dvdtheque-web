<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Box;
use App\Http\Requests\UpdateUser;
use App\Http\Resources\UserResource;
use App\Http\Resources\BoxResource;
use Illuminate\Support\Facades\Hash;
use App\Services\Dvdfr;

class BoxController extends ApiController
{
    /**
     * @OA\Post(
     *     tags={"Boxes"},
     *     path="/boxes",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *          required=true,
     *          description="The Token Request",
     *          @OA\JsonContent(
     *              @OA\Property(property="bar_code",type="string",example="3512392506697")
     *          )
     *     ),
     *      @OA\Response(
     *         response=201,
     *         description="Get box informations",
     *         @OA\JsonContent(ref="#/components/schemas/Box")
     *     ),
     *     @OA\Response(response=404, description="Box not found"),
     *     @OA\Response(response=422, description="Bad informations"),
     * )
     */
    public function store(Request $request)
    {
        $box = Box::where('bar_code', $request->bar_code)->first();
        if (!$box) {
            $box = Dvdfr::store($request->bar_code);
        }
        return new BoxResource($box);
    }

    /**
     * @OA\Post(
     *     tags={"Boxes"},
     *     path="/me/boxes/{id}",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(in="path", name="id"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="wishlist",type="boolean",example=false)
     *          )
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Get box informations",
     *         @OA\JsonContent(ref="#/components/schemas/Box")
     *     ),
     *     @OA\Response(response=404, description="Box not found"),
     *     @OA\Response(response=422, description="Bad informations"),
     * )
     */
    public function addToAuthUser(Box $box, Request $request)
    {
        Auth::user()->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => $request->wishlist]
        ]);
        return new BoxResource($box);
    }

    /**
     * @OA\Delete(
     *     tags={"Boxes"},
     *     path="/me/boxes/{id}",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(in="path", name="id"),
     *     @OA\Response(response=204, description="Box removed from user list"),
     *     @OA\Response(response=404, description="Box not found"),
     *     @OA\Response(response=422, description="Bad informations"),
     * )
     */
    public function deleteFromAuthUser(Box $box, Request $request)
    {
        Auth::user()->boxes()->detach($box->id);
    }
}