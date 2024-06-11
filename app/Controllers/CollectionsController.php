<?php

namespace Controllers;

use Collator;
use Helpers\CurrencyHelper;
use Helpers\WeatherHelper;
use Helpers\RegionsHelper;
use Helpers\WeatherStatuses;

class CollectionsController extends Controller {

    public function indexAction($f3, $params)
    {
        $alias = $params["collection_alias"];
        $alias = htmlspecialchars($alias);

        $check = $this->DB->exec("SELECT * FROM `collections` WHERE `alias` = '$alias';");
        if(!$check)
        {
            http_response_code(404);
            echo \View::instance()->render('templates/errors/404.htm');
            exit;
        }

        $collection_name = $check[0]['name'];
        $collection_id = $check[0]['id'];

        $catlist = $this->DB->exec(
            'SELECT * FROM `categories`'
        );

        $categories = [];
        foreach ($catlist as $cat) {
            $categories[$cat['id']]['name'] = $cat['name'];
            $categories[$cat['id']]['alias'] = $cat['alias'];
            if ($cat['alias'] === $alias) {
                $cat_id = $cat['id'];
                $cat_name = $cat['name'];
            }
        }

        $partners = $this->DB->exec("SELECT * FROM `partners` WHERE `balance` >= 0.4");
        $allowed_partners = '';
        foreach ($partners as $partner_key => $partner) {
            $partner_id = $partner['id'];
            $clicks_count = $this->DB->exec("SELECT COUNT(*) AS `clicks_count` FROM `partners_clicks` WHERE `partner_id` = $partner_id AND DATE(datetime) = CURDATE()")[0]['clicks_count'];
            if ($partner['day_limit'] < $clicks_count) {
                unset($partners[$partner_key]);
            } else {
                $allowed_partners .= $partner_id . ',';
            }
        }
        $allowed_partners = rtrim($allowed_partners, ',');
        $allowedQuery = "AND `source` IN ($allowed_partners)";
        $justGoodQuery = '';
        if($this->justgood)
        {
            $justGoodQuery = 'AND `is_good` = 1';
        }

        //SEO
        $f3->set('page_title', $collection_name . ' - всі новини в Україні');
        $f3->set('description_seo', ' Всі новини від українських ЗМІ в підбірці ' . $collection_name);
        $f3->set('collection_name', $collection_name);

        $news = $this->DB->exec("SELECT n.*, p.name AS source_name FROM news n JOIN collections_news c ON n.id = c.news_id INNER JOIN partners p ON p.id = n.source WHERE c.collections_id = $collection_id $justGoodQuery $allowedQuery");

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
        $f3->set('content', 'collection.htm');

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