<?php

namespace Controllers;

use Collator;
use Helpers\CurrencyHelper;
use Helpers\WeatherHelper;
use Helpers\RegionsHelper;
use Helpers\WeatherStatuses;

class SearchController extends Controller {

    public function indexAction($f3)
    {
        $s_query = $_POST["s_query"];
        $s_query = htmlspecialchars($s_query);

        $errors = [];
        if (mb_strlen($s_query) < 3) {
            $errors[] = 'Занадто короткий запит. Спробуйте вказати не менше 3 символів для пошуку.';
            //SEO
            $f3->set('page_title', 'Занадто короткий запит');
            $f3->set('description_seo', '');
        } elseif (mb_strlen($s_query) > 50) {
            $errors[] = 'Занадто довгий запит. Спробуйте вказати не більше 50 символів для пошуку.';
            //SEO
            $f3->set('page_title', 'Занадто довгий запит');
            $f3->set('description_seo', '');
        } 

        $news = [];

        if (count($errors) < 1) {
            $news = $this->DB->exec("SELECT * FROM `news` WHERE `title` LIKE '%$s_query%' ORDER BY created_at DESC LIMIT 50;");
            //SEO
            $f3->set('page_title', '«' . $s_query . '» - всі новини за запитом');
            $f3->set('description_seo', 'Пошук всіх новин в Україні за запитом «' . $s_query . '»');
            if (count($news) < 1) {
                $errors[] = 'За вашим запитом новин не знайдено.';
            }
        }

        $catlist = $this->DB->exec(
            'SELECT * FROM `categories`'
        );

        $categories = [];
        foreach ($catlist as $cat) {
            $categories[$cat['id']]['name'] = $cat['name'];
            $categories[$cat['id']]['alias'] = $cat['alias'];
        }

        $exchange_rate = $this->DB->exec("SELECT * FROM `cources`");
        $f3->set('exchange_rate', $exchange_rate[0]);

        $region_weather = new \DB\SQL\Mapper($this->DB, 'weather');
        $region_weather = $region_weather->load(array('region_in=?', $this->region));

        $weather = [
            "weather" => $region_weather,
            "icon" => WeatherHelper::STATUSES[$region_weather["icon"]]
        ];

        $f3->set('news', $news);
        $f3->set('errors', $errors);
        $f3->set('regions', RegionsHelper::getRegions());
        $f3->set('region', $this->region);

        $f3->set('weather', $weather);
        $f3->set('categories', $categories);
        $f3->set('content', 'search.htm');

        echo \View::instance()->render('templates/layout.htm');

    }

    public function setRegion($f3, $params)
    {
        $regionID = $f3->get('POST.regionID');
        setcookie("region", $regionID, time() + 3600 * 24 * 12);
        $f3->reroute('/');
    }
    
}

?>