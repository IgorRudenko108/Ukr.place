<?php

namespace Helpers;

use simplehtmldom\HtmlDocument;

class HoroscopeHelper
{
    const ZodiacSigns = [
        [
            "sign" => "aries",
            "name" => "Овен"
        ],
        [
            "sign" => "taurus",
            "name" => "Телець"
        ],
        [
            "sign" => "gemini",
            "name" => "Близнюки"
        ],        [
            "sign" => "cancer",
            "name" => "Рак"
        ],        [
            "sign" => "leo",
            "name" => "Лев"
        ],
        [
            "sign" => "virgo",
            "name" => "Діва"
        ],
        [
            "sign" => "libra",
            "name" => "Терези"
        ],
        [
            "sign" => "scorpio",
            "name" => "Скорпіон"
        ],
        [
            "sign" => "sagittarius",
            "name" => "Стрілець"
        ],
        [
            "sign" => "capricorn",
            "name" => "Козеріг"
        ],
        [
            "sign" => "aquarius",
            "name" => "Водолій"
        ],
        [
            "sign" => "pisces",
            "name" => "Риби"
        ]
    ];

    public static function getHoroscopeText($id)
    {
        $get_text = file_get_contents("https://fakty.ua/horoscope/$id");
        $doc = new HtmlDocument($get_text);
        $text = $doc->find('body > div.fon_kartinka > div > div.page_content_mainb > div.titl_main > div.fl_l.left_main > div > div.hor > div > div > p');
        $text = $text[0]->plaintext;
        return $text;
    }
}