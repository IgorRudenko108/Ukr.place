<?php

namespace Controllers;

use Collator;
use Helpers\CurrencyHelper;
use Helpers\RegionsHelper;
use Helpers\WeatherHelper;
use Helpers\WeatherStatuses;
use Helpers\HoroscopeHelper;

class MainController extends Controller {

    public function indexAction($f3)
    {
        //SEO
        $f3->set('page_title', 'Ukr.place - місце збору справжніх українців!');
        $f3->set('description_seo', 'Всі головні новини України, електронна пошта нового покоління і повна картина дня.');
        
        $f3->set('current_route', $f3->hive()["PATH"]); //CURRENT ROUTE

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

        $justGoodQuery = '';
        if($this->justgood)
        {
            $justGoodQuery = 'AND `is_good` = 1';
        }

        $allowedQuery = "AND `source` IN ($allowed_partners)";

        $news = [];
        $news['top'] = $this->DB->exec(
            "SELECT news.*, partners.name AS source_name FROM `news` INNER JOIN `partners` ON partners.id = news.source WHERE news.top = 1 $justGoodQuery $allowedQuery ORDER BY news.id DESC LIMIT 8");

        $news['region'] = $this->DB->exec(
            "SELECT news.*, partners.name AS source_name FROM `news` INNER JOIN `partners` ON partners.id = news.source WHERE news.region = $this->region $justGoodQuery $allowedQuery ORDER BY news.created_at DESC LIMIT 5");

        $not_ids = [];
        foreach ($news['top'] as $item) {
            $not_ids[] = $item['id'];
        }

        foreach ($news['region'] as $item) {
            $not_ids[] = $item['id'];
        }

        $not_ids = implode(',', $not_ids);

        $news['last'] = $this->DB->exec(
            "SELECT news.*, partners.name AS source_name FROM `news` INNER JOIN `partners` ON partners.id = news.source WHERE news.id NOT IN ($not_ids) $justGoodQuery $allowedQuery ORDER BY news.created_at DESC LIMIT 25");

        $catlist = $this->DB->exec(
            'SELECT * FROM `categories`'
        );

        $categories = [];
        foreach ($catlist as $cat) {
            $categories[$cat['id']]['name'] = $cat['name'];
            $categories[$cat['id']]['alias'] = $cat['alias'];
        }

        /* Telegram channels and posts */

        $tg_posts = [];
        $parsedown = new \Parsedown();

        $posts = $this->DB->exec("SELECT * FROM `tg_posts` ORDER BY `id` DESC LIMIT 5");
        foreach($posts as $key => $post)
        {
            $channelID = $post["source_id"];
            $channel = $this->DB->exec("SELECT * FROM `tg_sources` WHERE `id` = $channelID");
            $channel = $channel[0];
            
            $postData = [
                "id" => $post["id"],
                "channel_name" => $channel["name"],
                "channel_photo" => $channel["image"],
                "text" => $parsedown->text($post["full_text"])
            ];
            $tg_posts[] = $postData;

        }

        $exchange_rate = $this->DB->exec("SELECT * FROM `cources`");
        $f3->set('exchange_rate', $exchange_rate[0]);

        $region_weather = new \DB\SQL\Mapper($this->DB, 'weather');
        $region_weather = $region_weather->load(array('region_in=?', $this->region));

        $weather = [
            "weather" => $region_weather,
            "icon" => WeatherHelper::STATUSES[$region_weather["icon"]]
        ];

        //Get Astrology
        $horoscopeList = HoroscopeHelper::ZodiacSigns;

        $myHoroscopeText = $this->DB->exec("SELECT `text` FROM `horoscope` WHERE `zodiac_id` = " . $this->myzodiac)[0]["text"];
        $myHoroscopeImage = HoroscopeHelper::ZodiacSigns[$this->myzodiac-1]["sign"];
        $myHoroscopeName = HoroscopeHelper::ZodiacSigns[$this->myzodiac-1]["name"];

        $myHoroscop = [
            "image" => $myHoroscopeImage,
            "text" => html_entity_decode($myHoroscopeText),
            "name" => $myHoroscopeName
        ];

        $collections = $this->DB->exec("SELECT * FROM `collections` WHERE `active` = 1");

        $f3->set('news', $news);
        $f3->set('regions', RegionsHelper::getRegions());
        $f3->set('region', $this->region);

        $f3->set('myzodiac', $this->myzodiac);
        $f3->set('myHoroscop', $myHoroscop);

        $f3->set('justgood', $this->justgood);

        $f3->set('weather', $weather);
        $f3->set('categories', $categories);
        $f3->set('tg_posts', $tg_posts);
        $f3->set('collections', $collections);
        $f3->set('horosopeList', $horoscopeList);
        $f3->set('content', 'main.htm');

        echo \View::instance()->render('templates/layout.htm');

    }

    public function save($f3)
    {
        $data = $f3->get('POST.test');
        print_r($data);
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

    public function setJustGood($f3, $params)
    {
        $justgood = $f3->get('POST.justgood');
        if($justgood == "true")
        {
            $justgood = true;
        } else {
            $justgood = false;
        }

        setcookie("justgood", $justgood, time() + 3600 * 24 * 12);
    }

    public function loadTelegramPost($f3, $params)
    {
        $postID = (int)$f3->get('GET.id');

        $post = new \DB\SQL\Mapper($this->DB, 'tg_posts');
        $post = $post->load(array('id=?', $postID));

        $parsedown = new \Parsedown();
        $full_text = $parsedown->text($post->full_text);
        echo $full_text;
    }
}

?>