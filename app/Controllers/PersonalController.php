<?php

namespace Controllers;

use Collator;
use Helpers\CurrencyHelper;
use Helpers\WeatherHelper;
use Helpers\RegionsHelper;
use Helpers\WeatherStatuses;

class PersonalController extends Controller {

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
        $f3->set('page_title', 'Персональна стрічка новин');
        $f3->set('description_seo', 'Обрані категорії, регіони і теми.');

        $news = [];

        $loocked_ids = implode(',', $this->looked_news);

        if(!empty($loocked_ids))
        {
            $news['last'] = $this->DB->exec(
                "SELECT * FROM `news` WHERE `id` IN ($loocked_ids) LIMIT 50"
            );
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
        $f3->set('content', 'personal.htm');

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