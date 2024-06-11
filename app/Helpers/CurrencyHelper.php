<?php

namespace Helpers;

class CurrencyHelper
{
    public static function getCurrencies()
    {
        $api = file_get_contents("https://api.monobank.ua/bank/currency");
        $data = json_decode($api);
        $cources = [
            "USD" => $data[0]->rateBuy,
            "EUR" => $data[1]->rateBuy
        ];
        return $cources;
    }
}