<?php

namespace Controllers;

use Collator;
use Helpers\NewsHelper;

class NewsController extends Controller
{
    public function articleByAlias($f3, $params)
    {
        $alias = (int)$params["alias"];

        $this->looked_news[] = $alias;
        if(count($this->looked_news) >= 50)
        {
            unset($this->looked_news[49]);
        }
        $loocked_cookie = implode(",", $this->looked_news);
        setcookie("looked_news", $loocked_cookie, time() + 3600 * 24 * 12, '/');

        $article = new \DB\SQL\Mapper($this->DB, 'news');
        $article->load(array('id=?', $alias));
        $source = $article['source'];

        $partner = $this->DB->exec("SELECT * FROM `partners` WHERE `id` = :source",
            [
                ":source" => $source,
            ]
        )[0];

        $catlist = $this->DB->exec("SELECT * FROM `categories`");
        foreach ($catlist as $cat) {
            $categories[$cat['id']]['name'] = $cat['name'];
            $categories[$cat['id']]['alias'] = $cat['alias'];
        }

        $article["full_text"] = str_replace("&amp;", "&", $article["full_text"]);

        $this->f3->set('article', $article);
        $this->f3->set('partner', $partner);
        $this->f3->set('categories', $categories);

        echo \View::instance()->render('templates/news-details.htm');
    }

    public function goPartner($f3, $params)
    {
        $alias = (int)$params["alias"];
        $news = $this->DB->exec("SELECT * FROM `news` WHERE `id` = :id",
            [
                ":id" => $alias,
            ]
        )[0];
        $source_link = $news['source_link'];
        $partner_id = $news['source'];
        if (!$partner_id) {
            header("Location: /404");
            exit;
        }
        $partner = $this->DB->exec("SELECT * FROM `partners` WHERE `id` = :id",
            [
                ":id" => $partner_id,
            ]
        )[0];
        if (!$partner) {
            header("Location: /404");
            exit;
        }
        $click_price = $partner['click_price'];

        if ($partner['balance'] <= $click_price) {
            header("Location: /404");
            exit;
        }

        $this->DB->exec("INSERT INTO `partners_clicks` (`partner_id`, `news_id`) VALUES ('$partner_id', '$alias')");
        $this->DB->exec("UPDATE `partners` SET `balance` = `balance` - $click_price WHERE `id` = '$partner_id'");

        header("Location: $source_link");
    }

    public function apiSearch($f3)
    {
        $search = $f3->get('GET.search');
        $data = $this->DB->exec("SELECT id,title FROM `news` WHERE `title` LIKE '%$search%'");
        
        $return = [];

        foreach($data as $d)
        {
            $return[] = [
                "label" => $d["title"],
                "value" => $d["id"]
            ];
        }

        $data = json_encode($return);
        header("Content-Type: application/json");
        echo $data;
    }

    public function loadMoreNews($f3, $params)
    {
        $lastID = $f3->get('GET.lastid');


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

        if($f3->exists('GET.collection'))
        {
            $collection = (int)$f3->get('GET.collection');
            $query = "SELECT GROUP_CONCAT(news_id SEPARATOR ',') AS news_ids FROM `collections_news` WHERE collections_id = " . $collection;
            $collection_id_list = $this->DB->exec($query)[0]['news_ids'];

            $news = $this->DB->exec(
                "SELECT `id`,`title`,`category_id`,`created_at`,`img` FROM `news` WHERE id IN ($collection_id_list) AND id < $lastID $justGoodQuery ORDER BY id DESC LIMIT 10"
            );

        } else {
            $news = $this->DB->exec("SELECT `id`,`title`,`category_id`,`created_at`,`img` FROM `news` WHERE `id` < $lastID $justGoodQuery $allowedQuery ORDER BY id DESC LIMIT 10");
        }
        

        $catlist = $this->DB->exec("SELECT * FROM `categories`");
        foreach ($catlist as $cat) {
            $categories[$cat['id']] = $cat['name'];
        }
       
        foreach($news as $key => $article)
        {
            $news[$key]["category_name"] = $categories[$article["category_id"]];
            if (date('d.m.Y', strtotime($article['created_at'])) === date('d.m.Y', time())) {
                $news[$key]["time"] = date('H:i', strtotime($article['created_at']));
            } else {
                $t = date('d.m, H:i', strtotime($article['created_at']));
                $t = str_replace(",", '<br>', $t);

                $news[$key]["time"] = $t;
                
            }
            unset($news[$key]["category_id"]);
            unset($news[$key]["created_at"]);
        }
        
        header("Content-Type: application/json");
        $news = json_encode($news);
        echo $news;
    }

    public function articleLike($f3, $params)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $articleId = $_POST['id'];

            if (isset($articleId) && is_numeric($articleId)) {
                $check = $this->DB->exec("SELECT * FROM `news` WHERE `id` = '$articleId';");
                if($check)
                {
                    $this->DB->exec("UPDATE `news` SET `likes` = `likes` + 1 WHERE `id` = '$articleId';");
                    echo json_encode(['status' => 'success']);
                } else {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Invalid article ID']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid article ID']);
            }
        } else {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }
    }
}