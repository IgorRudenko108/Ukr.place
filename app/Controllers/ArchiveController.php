<?php

namespace Controllers;

use Collator;
use Helpers\CurrencyHelper;
use Helpers\WeatherHelper;
use Helpers\RegionsHelper;
use Helpers\WeatherStatuses;

class ArchiveController extends Controller {

    public function indexAction($f3, $params)
    {

        $catlist = $this->DB->exec(
            'SELECT * FROM `categories`'
        );

        $categories = [];
        foreach ($catlist as $cat) {
            $categories[$cat['id']]['name'] = $cat['name'];
            $categories[$cat['id']]['alias'] = $cat['alias'];
        }

        //SEO
        $f3->set('page_title', 'Архів новин, які ви переглянули');
        $f3->set('description_seo', 'Перелік новин, які ви переглянули останнім часом.');

        $news = [];

        $loocked_ids = implode(',', $this->looked_news);

        if(!empty($loocked_ids))
        {
            $news['last'] = $this->DB->exec(
                "SELECT news.*, partners.name AS source_name FROM `news` INNER JOIN `partners` ON partners.id = news.source WHERE news.id IN ($loocked_ids) LIMIT 50");
        } else {
            $news['last'] = [];
        }

        $news['last_top'] = [];

        $exchange_rate = $this->DB->exec("SELECT * FROM `cources`");
        $f3->set('exchange_rate', $exchange_rate[0]);

        $region_weather = new \DB\SQL\Mapper($this->DB, 'weather');
        $region_weather = $region_weather->load(array('region_in=?', $this->region));

        $weather = [
            "weather" => $region_weather,
            "icon" => WeatherHelper::STATUSES[$region_weather["icon"]]
        ];

        $f3->set('news', $news);
        $f3->set('regions', RegionsHelper::getRegions());
        $f3->set('region', $this->region);

        $f3->set('weather', $weather);
        $f3->set('categories', $categories);
        $f3->set('content', 'archive.htm');

        echo \View::instance()->render('templates/layout.htm');

    }

    public function setRegion($f3, $params)
    {
        $regionID = $f3->get('POST.regionID');
        setcookie("region", $regionID, time() + 3600 * 24 * 12);
        $f3->reroute('/');
    }

    public function setZodiac($f3, $params)
    {
        $zodiacID = $f3->get('POST.zodiacID');
        setcookie("zodiac", $zodiacID, time() + 3600 * 24 * 12);
        $f3->reroute('/');
    }
    
}

?>