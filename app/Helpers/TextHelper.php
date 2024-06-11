<?php

namespace Helpers;

class TextHelper
{
    public static function remove_images($html)
    {
        if(empty($html))
        {
            return "";
        }
        $pattern = '/<img[^>]+>/i'; // регулярное выражение для поиска тегов img в HTML
        $replacement = ''; // замена тегов img на пустую строку
        $resp = preg_replace($pattern, $replacement, $html); // применяем регулярное выражение и возвращаем измененный HTML
        return trim($resp);
    }
    public static function remove_links($text)
    {
        if(empty($text))
        {
            return "";
        }
        /*$pattern = '/<a\s.*?>(.*?)<\/a>/i'; // регулярное выражение для поиска ссылок в HTML
        $replacement = '$1'; // замена ссылок на текст, который находится между тегами <a> и </a>
        $resp = preg_replace($pattern, $replacement, $text); // применяем регулярное выражение и возвращаем измененный текст
        return trim($resp);*/

        $pattern = '/<a\s+[^>]*>(.*?)<\/a>/is';
        $callback = function($matches) {
            return $matches[1];
        };
        $result = preg_replace_callback($pattern, $callback, $text);

        $pattern_br = '/<br\s*\/?>/i';
        $result = preg_replace($pattern_br, '', $result);

        return trim($result);
    }
}