<?php

namespace App\Services;

use App\Models\Box;
use App\Models\Kind;
use App\Models\Celebrity;
use Illuminate\Support\Facades\Http;

class Tmdb
{
    public static function downloadPersonPhoto($celebrity)
    {
        if (!$celebrity->getMedia('photo')->first()) {
            $url_image = self::getPhotoUrl($celebrity->name);
            if ($url_image) {
                $celebrity->addMediaFromUrl($url_image)->toMediaCollection('photo');
            }
        }
    }

    private static function getPhotoUrl($name) 
    {
        $url = 'https://api.themoviedb.org/3/search/person?query='.rawurlencode($name).'&include_adult=false&language=fr-Fr&page=1';
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('app.tmdb_token'),
            'accept' => 'application/json' 
        ])->get($url)->json();

        if (!empty($response['results'])) {
            $person = $response['results'][0];
            if (isset($person['profile_path'])) {
                return 'https://image.tmdb.org/t/p/w300_and_h450_bestv2'.$person['profile_path'];
            }
        }
        return null;
    }
}