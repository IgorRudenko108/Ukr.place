<?php

function get_noun_plural_form (int $number, string $one, string $two, string $many): string
{
    $number = (int) $number;
    $mod10 = $number % 10;
    $mod100 = $number % 100;

    switch (true) {
        case ($mod100 >= 11 && $mod100 <= 20):
            return $many;

        case ($mod10 > 5):
            return $many;

        case ($mod10 === 1):
            return $one;

        case ($mod10 >= 2 && $mod10 <= 4):
            return $two;

        default:
            return $many;
    }
}

//Функция подсчета времени с момента публикации
function get_pub_date ($date) {
    $cur_date = time();
    $pub_date = strtotime($date);
    $diff = $cur_date - $pub_date;
    $pub_date_convert = '';
    if ($diff <= 3600) {
        $diff_date = str_pad(floor(abs($diff / 60)), 1, "0", STR_PAD_LEFT);
        $pub_date_convert = $diff_date . get_noun_plural_form($diff_date, ' хвилину', ' хвилини', ' хвилин') . " тому";
    } elseif ($diff > 3600 AND $diff <= 86400) {
        $diff_date = str_pad(floor(abs($diff / 60 / 60)), 1, "0", STR_PAD_LEFT);
        $pub_date_convert = $diff_date . get_noun_plural_form($diff_date, ' годину', ' години', ' годин') . " тому";
    } elseif ($diff > 86400 AND $diff <= 2678400) {
        $diff_date = str_pad(floor(abs($diff / 60 / 60 / 24)), 1, "0", STR_PAD_LEFT); 
        $pub_date_convert = $diff_date . get_noun_plural_form($diff_date, ' день', ' дні', ' днів') . " тому";
    } elseif ($diff > 2678400) {
        $pub_date_convert = "Більше місяця тому";
    }
    return $pub_date_convert;
}

?>