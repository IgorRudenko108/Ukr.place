<?php

namespace Cron;

use Controllers\Admin\DefaultController;
use Helpers\NewsHelper;
use Helpers\RegionsHelper;
use Helpers\RssHelper;
use Helpers\TextHelper;
use Helpers\ChatGPTHelper;
use Helpers\CurrencyHelper;
use Helpers\WeatherHelper;
use Cron\Helpers\ConsoleHelper;
use Helpers\HoroscopeHelper;

class Cron extends DefaultController{

    public $console;
    public function __construct()
    {
        parent::__construct();
        $this->console = new ConsoleHelper();
    }

    /* Get all news by rss */
    public function UpdateNews()
    {
        try
        {

            $categories = $this->DB->exec("SELECT * FROM `categories` WHERE `id` != 14");

            $rssList = new \DB\SQL\Mapper($this->DB, 'partners_rss');
            $rssList->load(array('active = ?', 1));
            $rssList = $rssList->find();

            foreach ($rssList as $rss) {

                if($rss->active == 0) // Если RSS выключен.
                {
                    continue;
                }

                $partner = new \DB\SQL\Mapper($this->DB, 'partners');
                $partner->load(array('id=?', $rss->partner_id));

                if($partner->active == 0)
                {
                    break;
                }

                $regionID = $partner->region;
                $categoryID = $partner->only_category;

                //echo $partner->name . " - " . $categoryID . "\n"; exit;

                $rssList = \Feed::loadRss($rss->link);
                $key = 0;

                foreach ($rssList->item as $rssItem) {

                    try {

                        $title = RssHelper::parseTagsAndAttr($rssItem, $rss->tag_title);
                        $description = TextHelper::remove_images(RssHelper::parseTagsAndAttr($rssItem, $rss->tag_description));
                        if(mb_strlen($description) > 255)
                        {
                            $description = mb_substr($description, 0, 252) . "...";
                        }

                        $image = RssHelper::parseTagsAndAttr($rssItem, $rss->tag_image);
                        if($image)
                        {
                            if (str_starts_with($image, "//")) {
                                $image = str_replace("//", "http://", $image);
                            }  
                        }
                        $link = RssHelper::parseTagsAndAttr($rssItem, $rss->tag_link);
                        $full_text = TextHelper::remove_links(RssHelper::parseTagsAndAttr($rssItem, $rss->tag_full_text));
                        
                        $date = RssHelper::parseTagsAndAttr($rssItem, $rss->tag_date);
                        //Check old
                        $checkArticle = new \DB\SQL\Mapper($this->DB, 'news');
                        $count = $checkArticle->count(array('source_link=?', $link));
                        if ($count > 0) {
                            echo "In DB, skip\n";
                            continue;
                        }
                    } catch (\Exception $ex)
                    {
                        continue;
                    }

                    try {

                        $article = new \DB\SQL\Mapper($this->DB, 'news');
                        $article->title = $title;
                        $article->description = $description;
                        $article->full_text = $full_text;


                        if($regionID == 0)
                        {
                            $needRegion = true;
                        } else {
                            $needRegion = false;
                        }

                        if(!is_null($categoryID)) // Если категория известна, определять не нужно
                        {
                            if($needRegion) // При этом возможно нужно определить регион
                            {
                                $params = ChatGPTHelper::CategoryDefinition($title, [], $needRegion);
                            }
                            $article->category_id = $categoryID;
                        } else {
                            $params = ChatGPTHelper::CategoryDefinition($title, $categories, $needRegion);
                            $article->category_id = $params['category_id'];
                        }

                        if($regionID == 0)
                        {
                            if (isset($params['city_id']) && $params['city_id'] > 0)
                            {
                                $article->region = $params['city_id'];
                            } else {
                                $article->region = null;
                            }
                        } else {
                            $article->region = $regionID;
                        }

                        $article->img = $image;
                        $article->source = $rss->partner_id;
                        $article->source_link = $link;
                        $article->alias = NewsHelper::generateAlias($title);

                        /* Created at */
                        if (!is_null($date) && ctype_digit($date))
                        {
                            $date = date('Y-m-d H:i:s', $date);
                        } else {
                            $date = date('Y-m-d H:i:s', strtotime($date));
                        }
                        $article->created_at = $date;

                        $article->save();


                    } catch (\Exception $ex)
                    {
                        echo $ex->getMessage();
                        continue;
                    }

                    $key++;
                    $this->console->echoTask("Parsed #$key\n");
                    if($key >= 10)
                    {
                        break;
                    }
                }
            }
            $this->console->echoSuccess("Cron work done!");
        } catch (Exception $ex)
        {
            $this->console->echoFail($ex->getMessage());
        }
    }

    /* Update exchange rates */
    public function UpdateCourses()
    {
        try
        {
            $cources = CurrencyHelper::getCurrencies();
            $USD = $cources["USD"];
            $EUR = $cources["EUR"];

            $this->DB->exec("UPDATE `cources` SET `USD`='$USD',`EUR`='$EUR',`updated_at`=current_timestamp()");

            $this->console->echoSuccess("Exchange courses successfully updated.");

        } catch (Exception $ex)
        {
            $this->console->echoFail($ex->getMessage());
        }
    }

    /* Update weather rates */
    public function UpdateWeather()
    {
        $regions = RegionsHelper::getRegions();
        $arr = [];
        foreach ($regions as $key => $region) {
            $arr[] = [
                "id" => $key,
                "weather" => $region["weather"]
            ];
        }

        foreach ($arr as $city) {
            try {

                $get_weather = WeatherHelper::getWeather($city["weather"]);

                $weather = new \DB\SQL\Mapper($this->DB, 'weather');
                $weather->load(array('region_in=?', $city["id"]));

                $weather->region_in = $city["id"];
                $weather->temp = $get_weather["temp"];
                $weather->status = $get_weather["status"];
                $weather->description = $get_weather["description"];
                //Определение иконки
                $icon = "none";
                if(str_contains($get_weather["status"], "Ясно"))
                {
                    $icon = "clear";
                } elseif (str_contains($get_weather["status"], "Хмарно з проясненнями"))
                {
                    $icon = "sunclouds";
                } elseif (str_contains($get_weather["status"], "хмар"))
                {
                    $icon = "cloudy";
                } elseif (str_contains($get_weather["status"], "дощ"))
                {
                    $icon = "rain";
                } elseif (str_contains($get_weather["status"], "сніг"))
                {
                    $icon = "snow";
                } elseif (str_contains($get_weather["status"], "гроза"))
                {
                    $icon = "storm";
                }
                $weather->icon = $icon;
                $weather->updated_at = date('Y-m-d H:i:s');
                $weather->update();

                $this->console->echoTask("Parsed weather for " . $city["weather"] . "\n");

            } catch (\Exception $ex)
            {
                continue;
            }
        }
        $this->console->echoSuccess("Weather updated.");
    }

    public function UpdateHoroscope()
    {
        foreach (HoroscopeHelper::ZodiacSigns as $key => $sign)
        {
            $key++;
            $text = HoroscopeHelper::getHoroscopeText($key);
            $text = htmlspecialchars($text);

            $this->DB->exec("UPDATE `horoscope` SET `zodiac_id`='$key',`text`='$text',`updated_at`=current_timestamp() WHERE `id` = $key;");
            $this->console->echoTask("Parsed horoscope for " . $sign["name"] . "\n");
        }
        $this->console->echoSuccess("Horoscope updated.");
    }
}

