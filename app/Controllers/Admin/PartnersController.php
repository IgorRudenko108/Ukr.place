<?php

namespace Controllers\Admin;

use Controllers\Admin\DefaultController;
use Helpers\RssHelper;
use Helpers\TextHelper;
use Helpers\RegionsHelper;

class PartnersController extends DefaultController
{

    public function indexAction($f3, $params, $regions)
    {
        $regions = RegionsHelper::getRegions();
        $regions[0] = [
            'name' => ''
        ];

        $pages_db = new \DB\SQL\Mapper($this->DB, 'partners');
        
        $limit = 10;
        $page = \Pagination::findCurrentPage();
        $filter = array('status <> ?', 3);
        $option = array('order' => 'id DESC');
        $subset = $pages_db->paginate($page-1, $limit, null, $option);

        // build page links
        $pages = new \Pagination($subset['total'], $subset['limit']);
        // add some configuration if needed
        $pages->setTemplate('templates/admin/layout/pagebrowser.htm');
        // for template usage, serve generated pagebrowser to the hive
        $this->f3->set('pagebrowser', $pages->serve());

        $this->f3->set('template', 'partners/index.htm');
        $this->f3->set('partners', $subset);
        $this->f3->set('regions', $regions);
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function addAction($f3, $params)
    {
        $regions = RegionsHelper::getRegions();

        /* Categories list */

        $categories = $this->DB->exec("SELECT * FROM `categories`");

        $this->f3->set('regions', $regions);
        $this->f3->set('categories', $categories);
        $this->f3->set('template', 'partners/add.htm');
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function addActionPost($f3, $params)
    {
        $err = [];

        $logo = $f3->get('FILES.logo');

        $allowed_types = [
            "image/jpeg",
            "image/svg",
            "image/png"
        ];

        if(!empty($logo["name"]))
        {
            if(in_array($logo["type"], $allowed_types))
            {
                $ext = explode(".", $logo["name"]);
                $newname = md5($logo["name"] . time());
                $newname = substr($newname, 0, 10);
                $newname = $newname . "." . end($ext);
                $logo_file_name = "/assets/img/partners/$newname";
                move_uploaded_file($logo["tmp_name"], __DIR__ . "/../../..".$logo_file_name);
            } else {
                $err[] = "Невірний формат зображення.";
            }
        }

        if(count($err) > 0)
        {
            $this->f3->set('errors', $err);
            $this->f3->set('template', 'partners/add.htm');
            echo \View::instance()->render('templates/admin/layout/app.htm');
        } else {

            $name = $f3->get('POST.name');
            $url = $f3->get('POST.url');
            $click_price = $f3->get('POST.click_price');
            $active = $f3->get('POST.active');
            $priority = $f3->get('POST.priority');
            $day_limit = $f3->get('POST.day_limit');
            $month_limit = $f3->get('POST.month_limit');
            $region = $f3->get('POST.region');
            $only_category = $f3->get('POST.only_category');
            if (empty($only_category)) {
                $only_category = NULL;
            }

            $partner = new \DB\SQL\Mapper($this->DB, 'partners');
            $partner->name = $name;
            $partner->link = $url;
            if($click_price) $partner->click_price = $click_price;
            $partner->active = $active;
            $partner->priority = $priority;
            $partner->day_limit = $day_limit;
            $partner->month_limit = $month_limit;
            $partner->region = $region;
            $partner->only_category = $only_category;

            if(isset($logo_file_name))
            {
                $partner->logo = $logo_file_name;
            }

            //Generate password
            $password = $this->generatePassword();
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $partner->password = $hashed_password;
            $partner->clear_password = $password;


            $partner->save();

            header("Location: /admin/partners");
        }
    }

    private function generatePassword() {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ($i = 0; $i < 10; $i++) {
            $password .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $password;
    }

    public function editAction($f3, $params)
    {
        $regions = RegionsHelper::getRegions();

        /* Categories list */

        $categories = $this->DB->exec("SELECT * FROM `categories`");

        $id = $params["id"];
        $partner = new \DB\SQL\Mapper($this->DB,'partners');
        $partner->load(array('id=?', $id));

        $this->f3->set('partner', $partner);
        $this->f3->set('regions', $regions);
        $this->f3->set('categories', $categories);
        $this->f3->set('template', 'partners/edit.htm');
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function editActionPost($f3, $params)
    {
        $id = $params["id"];

        $clear_password = $f3->get('POST.clear_password');

        $partner = new \DB\SQL\Mapper($this->DB,'partners');
        $partner->load(array('id=?', $id));
        
        if($clear_password)
        {
            $partner->clear_password = null;
            $partner->save();
            $_SESSION["alert"] = "Пароль успішно видалено.";
            //$f3->set('SESSION.alert', "Пароль успішно видалено.");
            header("Location: /admin/partners/$id/edit");
        } else {

            $name = $f3->get('POST.name');
            $url = $f3->get('POST.url');
            $click_price = $f3->get('POST.click_price');
            $active = (int)$f3->get('POST.active');
            $priority = $f3->get('POST.priority');
            $day_limit = $f3->get('POST.day_limit');
            $month_limit = $f3->get('POST.month_limit');
            $balance = $f3->get('POST.balance');
            $region = $f3->get('POST.region');
            $only_category = (int)$f3->get('POST.only_category');

            if($only_category == 0)
            {
                $only_category = null;
            }

            $partner->name = $name;
            $partner->link = $url;
            if($click_price) $partner->click_price = $click_price;
            $partner->active = $active;
            $partner->priority = $priority;
            $partner->day_limit = $day_limit;
            $partner->month_limit = $month_limit;
            $partner->balance = $balance;
            $partner->region = $region;
            $partner->only_category = $only_category;

            $logo = $f3->get('FILES.logo');

            $allowed_types = [
                "image/jpeg",
                "image/png"
            ];

            $err = [];

            if(!empty($logo["name"]))
            {
                if(in_array($logo["type"], $allowed_types))
                {
                    unlink(__DIR__ . "/../../..".$partner->logo); //Delete old logo

                    $ext = explode(".", $logo["name"]);
                    $newname = md5($logo["name"] . time());
                    $newname = substr($newname, 0, 10);
                    $newname = $newname . "." . end($ext);
                    $logo_file_name = "/assets/img/partners/$newname";
                    move_uploaded_file($logo["tmp_name"], __DIR__ . "/../../..".$logo_file_name);

                } else {
                    $err[] = "Невірний формат зображення.";
                }
            }

            if(count($err) > 0)
            {
                $this->f3->set('errors', $err);
                $this->f3->set('template', 'partners/add.htm');
                echo \View::instance()->render('templates/admin/layout/app.htm');
            } else {

                if(isset($logo_file_name))
                {
                    $partner->logo = $logo_file_name;
                }

                $partner->save();
            }

            $_SESSION["alert"] = "Успішно змінено.";
            header("Location: /admin/partners/$id/edit");
        }
    }

    public function deleteAction($f3, $params)
    {
        $partner_id = $params["id"];

        $partner = new \DB\SQL\Mapper($this->DB, 'partners');
        $partner->load(array('id=?', $partner_id));

        $partner->erase();
        unlink(__DIR__ . "/../../..".$partner->logo); //Delete logo
        $_SESSION["alert"] = "Партнер успішно видалений.";
        header("Location: /admin/partners/");
    }

    public function rssAction($f3, $params)
    {
        $partner_id = $params["id"];
        
        $pages_db = new \DB\SQL\Mapper($this->DB, 'partners_rss');
        
        $limit = 10;
        $page = \Pagination::findCurrentPage();
        $filter = array('partner_id = ?', $partner_id);
        $option = array('order' => 'id DESC');
        $subset = $pages_db->paginate($page-1, $limit, $filter, $option);

        // build page links
        $pages = new \Pagination($subset['total'], $subset['limit']);
        // add some configuration if needed
        $pages->setTemplate('templates/admin/layout/pagebrowser.htm');
        // for template usage, serve generated pagebrowser to the hive
        $this->f3->set('pagebrowser', $pages->serve());

        $this->f3->set('template', 'partners/rss/index.htm');
        $this->f3->set('rssList', $subset);
        $this->f3->set('partnerID', $partner_id);
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function rssAddAction($f3, $params)
    {
        $partner_id = $params["id"];

        $this->f3->set('template', 'partners/rss/add.htm');
        $this->f3->set('partnerID', $partner_id);
        echo \View::instance()->render('templates/admin/layout/app.htm');

    }

    public function checkRss($f3, $params)
    {
        $link = $f3->get('POST.link');
        $rss = \Feed::loadRss($link);
        foreach($rss->item as $item)
        {
            $var = get_object_vars($item);
            $tags = [];
            $num = 0;
            foreach($var as $key => $value)
            {
                $tags[$num]["tag"] = $key;
                $attributes = $item->$key->attributes();
                foreach($attributes as $key => $attr)
                {
                    $tags[$num]["attributes"][] = $key;
                }
                
                $num++;
            }
        }

        $html = "";
        foreach($tags as $tag)
        {
            //print_r($tag); exit;
            $html .= "<option value='tag.".$tag['tag']."'>".$tag["tag"]."</option>";
            if(isset($tag["attributes"]))
            {
               foreach($tag["attributes"] as $attr)
                {
                    $html .= "<option value='tag.".$tag["tag"].".attr.$attr'>--".$attr."</option>";
                } 
            }
        }
        echo $html;
    }

    public function addRssPost($f3, $params)
    {
        $partner_id = $params["id"];

        $name = $f3->get('POST.name');
        $link = $f3->get('POST.link');
        $active = $f3->get('POST.active');

        $tag_link = $f3->get('POST.tag_link');
        $tag_title = $f3->get('POST.tag_title');
        $tag_description = $f3->get('POST.tag_description');
        $tag_image = $f3->get('POST.tag_image');
        $tag_full_text = $f3->get('POST.tag_full_text');
        $tag_date = $f3->get('POST.tag_date');

        $partner_rss = new \DB\SQL\Mapper($this->DB, 'partners_rss');
        $partner_rss->name = $name;
        $partner_rss->link = $link;
        $partner_rss->active = $active;
        $partner_rss->partner_id = $partner_id;

        $partner_rss->tag_link = $tag_link;
        $partner_rss->tag_title = $tag_title;
        $partner_rss->tag_description = $tag_description;
        $partner_rss->tag_image = $tag_image;
        $partner_rss->tag_full_text = $tag_full_text;
        $partner_rss->tag_date = $tag_date;

        $partner_rss->save();

        $_SESSION["alert"] = "RSS успішно додано.";
        header("Location: /admin/partners/$partner_id/rss");
    }

    public function deleteRss($f3, $params)
    {
        $partner_id = $params["id"];
        $rssid = $params["rssid"];

        $partner_rss = new \DB\SQL\Mapper($this->DB, 'partners_rss');
        $partner_rss->load(array('id=?', $rssid));

        $partner_rss->erase();
        $_SESSION["alert"] = "RSS успішно видалено.";
        header("Location: /admin/partners/$partner_id/rss");
    }

    public function parseRss($f3, $params)
    {
        $link = $f3->get('POST.link');

        $tag_link = $f3->get('POST.tag_link');
        $tag_title = $f3->get('POST.tag_title');
        $tag_description = $f3->get('POST.tag_description');
        $tag_image = $f3->get('POST.tag_image');
        $tag_full_text = $f3->get('POST.tag_full_text');
        $tag_date = $f3->get('POST.tag_date');

        //parse

        $rss = \Feed::loadRss($link);
        $html = "";
        $item = $rss->item[0];

        $title = RssHelper::parseTagsAndAttr($item, $tag_title);
        $description = TextHelper::remove_images(RssHelper::parseTagsAndAttr($item, $tag_description));
        $image = RssHelper::parseTagsAndAttr($item, $tag_image);
        $link = RssHelper::parseTagsAndAttr($item, $tag_link);
        $full_text = TextHelper::remove_links(RssHelper::parseTagsAndAttr($item, $tag_full_text));
        $date = RssHelper::parseTagsAndAttr($item, $tag_date);


        if (ctype_digit($date))
        {
            $date = date('d.m.Y - H:i', $date);
        } else {
            $date = date('d.m.Y - H:i', strtotime($date));
        }

        $html .="<h1>$title</h1><br>";
        $html .="<h5>$date</h5><br>";
        $html .="<img style='max-width: 100%;' src='$image'><br>";
        $html .="<p style='background: #eee; padding: 8px; margin-top: 10px;'>$description</p>";
        $html .= $full_text;
        $html .= '<br><a href="' . $link . '" target="_blank">Джерело</a>';

        echo $html;
    }

    public function editRssAction($f3, $params)
    {
        $partner_id = $params["id"];
        $rssid = $params["rssid"];

        $partner_rss = new \DB\SQL\Mapper($this->DB,'partners_rss');
        $partner_rss->load(array('id=?', $rssid));

        $this->f3->set('rss', $partner_rss);
        $this->f3->set('template', 'partners/rss/edit.htm');
        echo \View::instance()->render('templates/admin/layout/app.htm');

        echo $rssid;
    }

    public function editRssSaveAction($f3, $params)
    {
        $partner_id = $params["id"];
        $rssID = $params["rssid"];

        $name = $f3->get('POST.name');
        $link = $f3->get('POST.link');
        $active = $f3->get('POST.active');

        $tag_link = $f3->get('POST.tag_link');
        $tag_title = $f3->get('POST.tag_title');
        $tag_description = $f3->get('POST.tag_description');
        $tag_image = $f3->get('POST.tag_image');
        $tag_full_text = $f3->get('POST.tag_full_text');

        $partner_rss = new \DB\SQL\Mapper($this->DB, 'partners_rss');
        $partner_rss->load(array('id=?', $rssID));

        $partner_rss->name = $name;
        $partner_rss->link = $link;
        $partner_rss->active = $active;
        $partner_rss->partner_id = $partner_id;

        $partner_rss->tag_link = $tag_link;
        $partner_rss->tag_title = $tag_title;
        $partner_rss->tag_description = $tag_description;
        $partner_rss->tag_image = $tag_image;
        $partner_rss->tag_full_text = $tag_full_text;

        $partner_rss->update();

        $_SESSION["alert"] = "RSS успішно збережено.";
        header("Location: /admin/partners/$partner_id/rss");
    }

    public function setRssActive($f3, $params)
    {
        $id = (int)$f3->get('POST.id');
        $checked = (int)$f3->get('POST.checked');

        $channel = new \DB\SQL\Mapper($this->DB, 'partners_rss');
        $channel->load(array('id=?', $id));
        $channel->active = $checked;
        $channel->update();
        $data = [
            "success" => true,
            "active" => (bool)$checked
        ];
        header("Content-type: application/json");
        echo json_encode($data);
    }

    public function partnerStat($f3, $params)
    {
        $partner_id = $params["id"];
        $partner = $this->DB->exec("SELECT * FROM `partners` WHERE `id` LIKE :partner_id",
            [
                ":partner_id" => $partner_id
            ])[0];
        $partner['clicks_count_today'] = $this->DB->exec("SELECT COUNT(*) AS `clicks_count` FROM `partners_clicks` WHERE `partner_id` = $partner_id AND DATE(datetime) = CURDATE()")[0]['clicks_count'];
        $partner['clicks_count_yesterday'] = $this->DB->exec("SELECT COUNT(*) AS `clicks_count` FROM `partners_clicks` WHERE `partner_id` = $partner_id AND DATE(datetime) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)")[0]['clicks_count'];
        $partner['clicks_count_month'] = $this->DB->exec("SELECT COUNT(*) AS `clicks_count` FROM `partners_clicks` WHERE `partner_id` = $partner_id AND MONTH(datetime) = MONTH(CURDATE()) AND YEAR(datetime) = YEAR(CURDATE())")[0]['clicks_count'];
        $partner['newslist'] = $this->DB->exec("SELECT news.*, COUNT(partners_clicks.id) AS clicks_count FROM news LEFT JOIN partners_clicks ON news.id = partners_clicks.news_id WHERE news.source = $partner_id GROUP BY news.id ORDER BY news.created_at DESC LIMIT 100");

        $partner['news_count'] = $this->DB->exec("SELECT count(*) as `all_count` FROM `news` WHERE `source` = :partner_id",
            [
                ":partner_id" => $partner_id
            ])[0]['all_count'];

        $this->f3->set('template', 'partners/stat.htm');
        $this->f3->set('partner', $partner);
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }
}