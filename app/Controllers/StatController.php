<?php

namespace Controllers;

use Collator;

class StatController extends Controller {
    public function indexAction($f3, $params)
    {
        $partner_pass = $params["password"];
        $check = $this->DB->exec("SELECT * FROM `partners` WHERE `clear_password` LIKE :partner_pass",
            [
                ":partner_pass" => $partner_pass
            ]);
        if(!$check)
        {
            http_response_code(404);
            echo \View::instance()->render('templates/errors/404.htm');
            exit;
        }

        $partner_id = $check[0]['id'];
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

        $this->f3->set('partner', $partner);
        echo \View::instance()->render('templates/partner_stat.htm');
    }
}