<?php

namespace Helpers;

class NewsHelper
{

    /*public static function generateAlias($title)
    {
        $transliterated_str = transliterator_transliterate('Any-Latin; Latin-ASCII;', $title);
        $str = mb_strtolower($transliterated_str, 'UTF-8');
        $str = preg_replace('/[^a-zа-яё0-9]+/u', '-', $str);
        $str = trim($str, '-');
        return $str;
    }*/


    public static function generateAlias($text) {
        // Универсальная таблица транслитерации для украинского и русского языков
        $translit = array(
            'а'=>'a','б'=>'b','в'=>'v','г'=>'g','ґ'=>'g','д'=>'d','е'=>'e','ё'=>'yo','є'=>'ye','ж'=>'zh','з'=>'z','и'=>'i','і'=>'i','ї'=>'yi','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'kh','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya',
            'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Ґ'=>'G','Д'=>'D','Е'=>'E','Ё'=>'Yo','Є'=>'Ye','Ж'=>'Zh','З'=>'Z','И'=>'I','І'=>'I','Ї'=>'Yi','Й'=>'Y','К'=>'K','Л'=>'L','М'=>'M','Н'=>'N','О'=>'O','П'=>'P','Р'=>'R','С'=>'S','Т'=>'T','У'=>'U','Ф'=>'F','Х'=>'Kh','Ц'=>'Ts','Ч'=>'Ch','Ш'=>'Sh','Щ'=>'Shch','Ы'=>'Y','Э'=>'E','Ю'=>'Yu','Я'=>'Ya', 'ь' => '', 'Ь' => ''
        );

        // Заменяем пробелы на тире
        $text = str_replace(' ', '-', $text);

        // Удаляем знаки препинания
        $text = preg_replace('/[^\p{L}\p{N}\-]/u', '', $text);

        // Транслитерируем текст
        $text = strtr($text, $translit);

        $text = strtolower($text);

        return $text;
    }
}