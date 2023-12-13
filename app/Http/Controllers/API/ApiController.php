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

}