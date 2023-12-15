<?php

namespace App\Services;

use App\Models\Box;
use Illuminate\Support\Facades\Http;

class Dvdfr
{
    public static function store($bar_code)
    {
        $id = self::getId($bar_code);
        if (!$id) {
            return null;
        }
        return self::createWithId($id);
    }

    private static function getId($bar_code) 
    {
        $url = 'https://www.dvdfr.com/api/search.php?gencode='.$bar_code;
        $response = Http::get($url);
        $response_content = $response->body();

        $xml = simplexml_load_string($response_content, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($xml->error || $xml->count() == 0) {
            return null;
        }

        if (!$xml->dvd[0]->id) {
            return null;
        }
        return $xml->dvd[0]->id;
    }

    private static function createWithId($dvdfr_id) 
    {
        $url = 'https://www.dvdfr.com/api/dvd.php?id='.$dvdfr_id;
        $response = Http::get($url);
        $response_content = $response->body();

        $xml = simplexml_load_string($response_content, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($xml->error || $xml->count() == 0) {
            return null;
        }
        $format = (string) $xml->media;
        $title = (string) $xml->titres->fr;
        $ean = (string) $xml->ean;
        $original_title = (string) $xml->titres->vo;
        $original_title = empty($original_title) ? null : $original_title;
        $year  = (string) $xml->annee;
        $edition  = (string) $xml->edition;
        $edition = empty($edition) ? null : $edition;
        $editor  = (string) $xml->editeur;
        $editor = empty($editor) ? null : $editor;
        $synopsis  = (string) $xml->synopsis;
        $synopsis = empty($synopsis) ? null : $synopsis;
        $url_image = (string) $xml->cover;

        $box = Box::create([
            'type' => $format,
            'bar_code' => $ean,
            'title' => $title,
            'original_title' => $original_title,
            'year' => $year,
            'synopsis' => $synopsis,
            'edition' => $edition,
            'editor' => $editor,
            'dvdfr_id' => $dvdfr_id,
        ]);
        if ($url_image) {
            $box->addMediaFromUrl($url_image)->toMediaCollection('cover');
        }

        $list_bonus = (array) $xml->listeBonusHtml;
        if (!empty($list_bonus)) {
            foreach ($list_bonus['bonushtml'] as $bonus) {
                $matches = [];
                preg_match_all("/(?<=www.dvdfr.com\/dvd\/f)(.*?)(?=-)/i", $bonus, $matches);
                foreach ($matches[0] as $id) {
                    $sub_box = self::createWithId($id);
                    $box->boxes()->attach($sub_box);
                }
            }
        }

        return $box;
    }
}