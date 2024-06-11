<?php

namespace Helpers;

use simplehtmldom\HtmlDocument;

class WeatherHelper
{
    const STATUSES = [
        "none" => [
            "icon" => "cloudy",
        ],
        "clear" => [
            "icon" => "clear",
        ],
        "cloudy" => [
            "icon" => "cloudy",
        ],
        "sunclouds" => [
            "icon" => "sunclouds",
        ],
        "rain" => [
            "icon" => "rain",
        ],
        "storm" => [
            "icon" => "storm",
        ],
        "snow" => [
            "icon" => "snow",
        ]
    ];
    public static function getWeather($city)
    {
        try {
            $date = date('Y-m-d', time());
            $data = file_get_contents("https://ua.sinoptik.ua/погода-$city/$date?ajax=GetForecast");
            $doc = new HtmlDocument($data);
            $temp = $doc->find('.today-temp')[0]->plaintext;
            $temp = str_replace("°C", "", $temp);
            $status = $doc->find('img')[0]->alt;
            $description = $doc->find('div.description')[0]->plaintext;
            $return = [
                "temp" => trim($temp),
                "status" => trim($status),
                "description" => trim($description)
            ];
            return $return;
        } catch (\Exception $ex)
        {
            return [];
        }
    }
}