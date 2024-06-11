<?php

namespace Helpers;

class ChatGPTHelper
{
	public static function CategoryDefinition($title, $categoriesList = [], $needRegion = false)
	{
		$catlist = "";
		foreach($categoriesList as $category)
		{
			$catlist .= $category["id"] . ". ".$category["name"] . "\n";
		}
		/*$prompt = 'Определи категорию новости по заголовку "' . $title . '". Категорию выбери из списка и дай в ответ только цифру из нумерации списка категорий. Не текст, просто цифру, номер категории, без точки в конце. Вот список категорий:' . "\n\n" . $catlist;*/

		$reglist = '1. Київ, 2. Вінниця, 3. Дніпро, 4. Волинь, 5. Донецьк, 6. Житомир, 7. Запоріжжя, 8. Закарпаття, 9. Івано-Франківськ, 10. Кропивницький, 11. Луганськ, 12. Львів, 13. Миколаїв, 14. Одеса, 15. Полтава, 16. Рівне, 17. Суми, 18. Тернопіль, 19. Харків, 20. Херсон, 21. Хмельницький, 22. Черкаси, 23. Чернігів, 24. Чернівці, 25. Крим';

		//нужны промпты если нужно узнать категорию и регион, только категорию или только регион...
		if($needRegion)
		{
			if(count($categoriesList) > 0)
			{
				$prompt = 'Определи категорию новости по заголовку "' . $title . '". Категорию выбери из списка: ' . "\n\n" . $catlist . "\n\n" . 'Также, если в заголовке есть принадлежность к какому то городу или области из списка, определи к какому городу или области относится новость. Вот список городов: ' . "\n\n" . $reglist . "\n\n" . 'Ответ дай в таком формате: category = 1, city = 1. Если нет принадлежности к городу или области - в ответе укажи city = NULL.';
			} else {
				$prompt =  'Определи принадлежность к какому-то городу или области из списка, к какому городу или области относится новость. Вот список городов: ' . "\n\n" . $reglist . "\n\n" . 'Ответ дай в таком формате: city = 1. Если нет принадлежности к городу или области - в ответе укажи city = NULL.';
			}
			
		} else {
			if(count($categoriesList) > 0)
			{
                $prompt = 'Определи категорию новости по заголовку "' . $title . '". Категорию выбери из списка: ' . "\n\n" . $catlist . "\n\n" . 'Ответ дай в таком формате: category = 1';
			} else {
                $prompt =  'Определи принадлежность к какому-то городу или области из списка, к какому городу или области относится новость. Вот список городов: ' . "\n\n" . $reglist . "\n\n" . 'Ответ дай в таком формате: city = 1. Если нет принадлежности к городу или области - в ответе укажи city = NULL.';
            }
		}

		if(count($categoriesList) <= 0 && $needRegion) // Если не нужно определять категорию но нужно определить регион
		{
			$prompt =  'Определи принадлежность к какому-то городу или области из списка, к какому городу или области относится новость. Вот список городов: ' . "\n\n" . $reglist . "\n\n" . 'Ответ дай в таком формате: city = 1. Если нет принадлежности к городу или области - в ответе укажи city = NULL.';
		}
		

		$response = self::get_gpt3_response($prompt);
        if (isset($response["choices"][0]["message"])) {
            $return = trim($response["choices"][0]["message"]["content"]);
        } else {
            $return = "";
        }
		preg_match_all('/\d+/', $return, $matches);

		if (isset($matches[0][0])) {
			$category = intval($matches[0][0]);
		} else {
			$category = 0;
		}

		if (isset($matches[0][1])) {
			$city = intval($matches[0][1]);
		} else {
			$city = NULL;
		}
        
        
        $result = [];

		/*$return = preg_replace("/[^0-9]/", '', $return);
		$return = trim($return);*/
		try{
			$result['category_id'] = (int)$category;
			$result['city_id'] = (int)$city;
		} catch (Exception $ex)
		{
			$result['category_id'] = 0;
			$result['city_id'] = NULL;
		}
		return $result;
	}

	private static function get_gpt3_response($prompt) 
	{
		$request_data = array(
		    "model" => 'gpt-3.5-turbo',
		    "messages" => array(
		        array("role" => "system", "content" => "You are a helpful assistant."),
		        array("role" => "user", "content" => $prompt)
		    ),
		    "temperature" => 0.7,
		    "max_tokens" => 1000
		);

		$api_key = "sk-proj-RPTYY9Y0ZSxXwyOdy3wZT3BlbkFJn3jIzpks3DHnk9dqbFA8";

		$request_data_json = json_encode($request_data);

		$ch = curl_init("https://api.openai.com/v1/chat/completions");

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request_data_json);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    "Content-Type: application/json",
		    "Content-Length: " . strlen($request_data_json),
		    "Authorization: Bearer " . $api_key
		));

		$response = curl_exec($ch);

		curl_close($ch);

		$response_data = json_decode($response, true);

		return $response_data;
	}

}