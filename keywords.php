<?php 

$db_connect = mysqli_connect('localhost', 'root', '', 'ukrplace');
mysqli_set_charset($db_connect, 'utf8mb4' );

/**
 * @param $arrayWords - Массив слов полученный из текста
 * @param int $repeatWordCount - Учитывать слова с указанным количеством повторений
 * @param int $minWordLength - Учитывать слова с данным количеством символов
 *
 * @return array - Полученный массив с ключевыми словами
 */
function keywords($arrayWords, $repeatWordCount = 2, $minWordLength = 3) {
    $tmpArr = [];
    $resultArray = [];

    foreach ($arrayWords as $val) {
        if (strlen($val) >= $minWordLength) {
            $val = mb_strtolower($val);
            if (array_key_exists($val, $tmpArr)) {
                $tmpArr[$val]++;
            } else {
                $tmpArr[$val] = 1;
            }
        }

        if ($tmpArr[$val] >= $repeatWordCount) {;
            $resultArray[$val] = $tmpArr[$val];
        }
    }

    arsort($resultArray);

    return $resultArray;
}


$news = [];

$tmp = $db_connect->query("SELECT * FROM news ORDER BY id DESC LIMIT 10");
while ($res = $tmp->fetch_assoc()) {
	$news[] = $res;
}


$array = explode(' ', $news[2]['full_text']);
var_dump(array_slice(keywords($array, 2), 3, 10));


exit;

// Текст новости
$newsText = "Вчера в столице прошла крупная выставка собак. ...";

// Инициализация TextRank
$textRank = new TextRank();
$textRank->setStopWords(array('и', 'в', 'на', 'не', 'с', 'по')); // Установите свой список стоп-слов

// Извлечение ключевых слов
$keywords = $textRank->getKeywords($newsText);

// Вывод ключевых слов
foreach ($keywords as $keyword => $score) {
    echo $keyword . " (вес: " . $score . ")" . PHP_EOL;
}

echo '<pre>';
print_r($news);
echo '</pre>';

?>