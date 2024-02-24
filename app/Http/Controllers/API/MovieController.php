<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Resources\LightBoxResource;
use App\Http\Resources\MovieResource;
use App\Models\Box;

class MovieController extends ApiController
{
    /**
     * @OA\Get(
     *     tags={"Movies"},
     *     path="/me/movies",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(in="query", name="search"),
     *     @OA\Response(
     *         response=200,
     *         description="Get boxes associated to auth user",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/LightBox")),
     *              @OA\Property(property="links", type="object", 
     *                  @OA\Property(property="first", type="string"),
     *                  @OA\Property(property="last", type="string"),
     *                  @OA\Property(property="prev", type="string"),
     *                  @OA\Property(property="next", type="string"),
     *              ),
     *              @OA\Property(property="meta", type="object", 
     *                  @OA\Property(property="current_page", type="integer"),
     *                  @OA\Property(property="last_page", type="integer"),
     *                  @OA\Property(property="per_page", type="integer"),
     *                  @OA\Property(property="to", type="integer"),
     *                  @OA\Property(property="total", type="integer"),
     *              )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Bad informations"),
     * )
     */
    public function index(Request $request)
    {
        $boxes = Auth::user()->movies();
        if ($request->search) {
            $boxes->where(function ($query) use ($request) {
                $query->where('boxes.title', 'LIKE', '%'.$request->search.'%')
                    ->orWhere('boxes.original_title', 'LIKE', '%'.$request->search.'%')
                    ->orWhere('boxes.editor', 'LIKE', '%'.$request->search.'%')
                    ->orWhere('boxes.edition', 'LIKE', '%'.$request->search.'%')
                    ->orWhereIn('boxes.id', function ($query) use ($request) {
                        $query
                            ->select('box_celebrity.box_id')
                            ->from('box_celebrity')
                            ->join('celebrities', 'celebrities.id', '=', 'box_celebrity.celebrity_id')
                            ->where('name', 'LIKE', '%'.$request->search.'%')
                        ;
                    })
                    ->orWhereIn('boxes.id', function ($query) use ($request) {
                        $query
                            ->select('box_kind.box_id')
                            ->from('box_kind')
                            ->join('kinds', 'kinds.id', '=', 'box_kind.kind_id')
                            ->where('name', 'LIKE', '%'.$request->search.'%')
                        ;
                    });
            });
        }
        $boxes = $boxes->orderBy('boxes.title');
        $boxes = $boxes->paginate(config('app.item_per_page'));
        return LightBoxResource::collection($boxes);
    }

    /**
     * @OA\Get(
     *     tags={"Movies"},
     *     path="/movies/{id}",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(in="path", name="id"),
     *     @OA\Response(
     *         response=200,
     *         description="Get movie informations",
     *         @OA\JsonContent(ref="#/components/schemas/Movie")
     *     ),
     *     @OA\Response(response=404, description="Movie not found"),
     * )
     */
    public function show(Box $box)
    {
        return $this->returnSuccess(new MovieResource($box));
    }

}