<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

/**
 * @OA\Server(url="/api"),
 * @OA\Info(
 *      title="DVDThèque", 
 *      version="1.0",
 *      description="Documentation de l'API DVDThèque",
 * ),
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      in="header",
 *      name="bearerAuth",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="JWT",
 * ),
 */
class ApiController extends Controller
{
    public function returnSuccess($response = null)
    {
        return $this->returnResponse($response);
    }

    public function returnNotFound($response = null)
    {
        return $this->returnResponse($response, 404);
    }

    public function returnNoContent($response = null)
    {
        return $this->returnResponse($response, 204);
    }

    public function returnCreated($response = null)
    {
        return $this->returnResponse($response, 201);
    }

    public function returnResponse($response = null, $status = 200) 
    {
        return response($response, $status);
    }
}