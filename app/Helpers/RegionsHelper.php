<?php

namespace Helpers;

class RegionsHelper
{
	private const REGIONS = [
        1 => [
            'name' => 'Київ',
            'alias' => 'kyiv',
            'reg_title' => 'Київ і область',
            'weather' => 'київ'
        ],
        2 => [
            'name' => 'Вінниця',
            'alias' => 'vinnytsia',
            'reg_title' => 'Вінниця і область',
            'weather' => 'вінниця'
        ],
        3 => [
            'name' => 'Дніпро',
            'alias' => 'dnipro',
            'reg_title' => 'Дніпро і область',
            'weather' => 'дніпро'
        ],
        4 => [
            'name' => 'Волинь',
            'alias' => 'volyn',
            'reg_title' => 'Волинська область',
            'weather' => 'луцьк'
        ],
        5 => [
            'name' => 'Донецьк',
            'alias' => 'donetsk',
            'reg_title' => 'Донецьк і область',
            'weather' => 'донецьк'
        ],
        6 => [
            'name' => 'Житомир',
            'alias' => 'zhytomyr',
            'reg_title' => 'Житомир і область',
            'weather' => 'житомир'
        ],
        7 => [
            'name' => 'Запоріжжя',
            'alias' => 'zaporizhzhia',
            'reg_title' => 'Запоріжжя і область',
            'weather' => 'запоріжжя'
        ],
        8 => [
            'name' => 'Закарпаття',
            'alias' => 'zakarpattia',
            'reg_title' => 'Закарпатська область',
            'weather' => 'ужгород'
        ],
        9 => [
            'name' => 'Івано-Франківськ',
            'alias' => 'ivano-frankivsk',
            'reg_title' => 'Івано-Франківськ і область',
            'weather' => 'івано-франківськ'
        ],
        10 => [
            'name' => 'Кропивницький',
            'alias' => 'kropyvnytskyi',
            'reg_title' => 'Кропивницький і область',
            'weather' => 'кропивницький'
        ],
        11 => [
            'name' => 'Луганськ',
            'alias' => 'luhansk',
            'reg_title' => 'Луганськ і область',
            'weather' => 'луганськ'
        ],
        12 => [
            'name' => 'Львів',
            'alias' => 'lviv',
            'reg_title' => 'Львів і область',
            'weather' => 'львів'
        ],
        13 => [
            'name' => 'Миколаїв',
            'alias' => 'mykolaiv',
            'reg_title' => 'Миколаїв і область',
            'weather' => 'миколаїв'
        ],
        14 => [
            'name' => 'Одеса',
            'alias' => 'odesa',
            'reg_title' => 'Одеса і область',
            'weather' => 'одеса'
        ],
        15 => [
            'name' => 'Полтава',
            'alias' => 'poltava',
            'reg_title' => 'Полтава і область',
            'weather' => 'полтава'
        ],
        16 => [
            'name' => 'Рівне',
            'alias' => 'rivne',
            'reg_title' => 'Рівне і область',
            'weather' => 'рівне'
        ],
        17 => [
            'name' => 'Суми',
            'alias' => 'sumy',
            'reg_title' => 'Суми і область',
            'weather' => 'суми'
        ],
        18 => [
            'name' => 'Тернопіль',
            'alias' => 'ternopil',
            'reg_title' => 'Тернопіль і область',
            'weather' => 'тернопіль'
        ],
        19 => [
            'name' => 'Харків',
            'alias' => 'kharkiv',
            'reg_title' => 'Харків і область',
            'weather' => 'харків'
        ],
        20 => [
            'name' => 'Херсон',
            'alias' => 'kherson',
            'reg_title' => 'Херсон і область',
            'weather' => 'херсон'
        ],
        21 => [
            'name' => 'Хмельницький',
            'alias' => 'khmelnitskiy',
            'reg_title' => 'Хмельницький і область',
            'weather' => 'хмельницький'
        ],
        22 => [
            'name' => 'Черкаси',
            'alias' => 'cherkasy',
            'reg_title' => 'Черкаси і область',
            'weather' => 'черкаси'
        ],
        23 => [
            'name' => 'Чернігів',
            'alias' => 'chernigiv',
            'reg_title' => 'Чернігів і область',
            'weather' => 'чернігів'
        ],
        24 => [
            'name' => 'Чернівці',
            'alias' => 'chernivtsi',
            'reg_title' => 'Чернівці і область',
            'weather' => 'чернівці'
        ],
        25 => [
            'name' => 'Крим',
            'alias' => 'krym',
            'reg_title' => 'Крим',
            'weather' => 'сімферополь'
        ],
    ];

	public static function getRegions()
	{
		return self::REGIONS;
	}
}