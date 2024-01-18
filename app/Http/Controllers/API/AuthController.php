<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UpdateUser;
use App\Http\Requests\Register;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;

class AuthController extends ApiController
{
    /**
     * @OA\Post(
     *     tags={"Auth"},
     *     path="/login",
     *     @OA\RequestBody(
     *          required=true,
     *          description="The Token Request",
     *          @OA\JsonContent(
     *              @OA\Property(property="email",type="string",example="test@example.com"),
     *              @OA\Property(property="password",type="string",example="password"),
     *          )
     *     ),
     *     @OA\Response(response=200, description="Return access token", content={
     *          @OA\MediaType(mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(
     *                  property="token",
     *                  type="string",
     *                  description="Access token"
     *              ),
     *          ))
     *     }),
     * )
     */
    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => __('auth.failed')
            ], 422);
        }

        $user = User::where('email', $request['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;
        return $this->returnSuccess([
            'token' => $token,
        ]);
    }


    /**
     * @OA\Post(
     *     tags={"Auth"},
     *     path="/register",
     *     @OA\RequestBody(
     *          required=true,
     *          description="The Token Request",
     *          @OA\JsonContent(
     *              @OA\Property(property="email",type="string",example="test@example.com"),
     *              @OA\Property(property="name",type="string",example="John Doo"),
     *              @OA\Property(property="password",type="string",example="password"),
     *              @OA\Property(property="password_confirmation", type="string", example="password"),
     *          )
     *     ),
     *     @OA\Response(response=200, description="Return access token", content={
     *          @OA\MediaType(mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(
     *                  property="token",
     *                  type="string",
     *                  description="Access token"
     *              ),
     *          ))
     *     }),
     * )
     */
    public function register(Register $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;
        return $this->returnSuccess([
            'token' => $token,
        ]);
    }

    /**
     * @OA\Post(
     *     tags={"Auth"},
     *     path="/logout",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=204, description="User Logout"),
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->returnNoContent();
    }

    /**
     * @OA\Get(
     *     tags={"Auth"},
     *     path="/me",
     *     security={{"bearerAuth": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="Get auth user informations",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function me(Request $request)
    {
        return $this->returnSuccess(new UserResource(Auth::user()));
    }


    /**
     * @OA\Put(
     *     tags={"Auth"},
     *     path="/me",
     *     @OA\RequestBody(
     *          required=true,
     *          description="The user informations",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="test user 2"),
     *              @OA\Property(property="password", type="string", example="password"),
     *              @OA\Property(property="new_password", type="string", example="password2"),
     *              @OA\Property(property="new_password_confirmation", type="string", example="password2"),
     *          )
     *     ),
     *     security={{"bearerAuth": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="Updated user informations",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function update(UpdateUser $request)
    {
        if ($request->name) {
            Auth::user()->update([
                'name' => $request->name
            ]);
        }
        if ($request->new_password) {
            Auth::user()->update([
                'password' => Hash::make($request->new_password)
            ]);
        }
        return $this->returnSuccess(new UserResource(Auth::user()));
    }
}