<?php

namespace Controllers\Admin;

use Controllers\Admin\DefaultController;
use DateTime;
use Helpers\RegionsHelper;

class ChartsController extends DefaultController
{
    private $monthNamesUkr = [
        'січня', 'лютого', 'березня', 'квітня', 'травня', 'червня', 'липня',
        'серпня', 'вересня', 'жовтня', 'листопада', 'грудня'
    ];
    public function getCountByLastMonth($f3, $params)
    {
        $table = $params["table"];
        $getLastMonthCount = $this->DB->exec("SELECT DATE_FORMAT(created_at, '%d-%m-%Y') AS day, COUNT(*) AS count FROM `$table` WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01') GROUP BY day");
        $labels = [];
        $data = [];
        foreach ($getLastMonthCount as &$row) {
            $dateParts = explode('-', $row['day']);
            $month = intval($dateParts[1]);
            $monthName = $this->monthNamesUkr[$month - 1];
            $label = $dateParts[0] . ' ' . $monthName;
            $labels[] = $label;
            $data[] = $row['count'];
        }

        $resp = [
            "labels" => $labels,
            "data" => $data
        ];

        header("Content-type: application/json");
        $resp = json_encode($resp);
        echo $resp;
    }

    public function percentage_good_news($f3, $params)
    {
        $data = $this->DB->exec("SELECT (SUM(is_good = 1) / COUNT(*)) * 100 AS is_good_percentage, (SUM(is_good = 0) / COUNT(*)) * 100 AS is_bad_percentage FROM `news`;");
        $data = $data[0];

        $d = [];

        $d[] = number_format($data["is_good_percentage"], 1, '.', ' ');
        $d[] = number_format($data["is_bad_percentage"], 1, '.', ' ');

        $resp = [
            "labels" => ["Гарних новин", "Поганих новин"],
            "data" => $d
        ];
        header("Content-type: application/json");
        $resp = json_encode($resp);
        echo $resp;
    }

    public function percentage_regions_news($f3, $params)
    {

        $data = $this->DB->exec("SELECT region, (COUNT(*) / (SELECT COUNT(*) FROM news)) * 100 AS region_percentage FROM news GROUP BY region;");

        $regions = RegionsHelper::getRegions();

        $labels = [];
        $resp_data = [];
        foreach ($data as $d)
        {
            if(empty($d["region"]))
            {
                $regionName = "Не регіональні";
            } else {
                $regionName = $regions[$d["region"]]["name"];
            }
            $labels[] = $regionName;
            $resp_data[] = number_format($d["region_percentage"], 1, '.', ' ');
        }

        $resp = [
            "labels" => $labels,
            "data" => $resp_data
        ];
        header("Content-type: application/json");
        $resp = json_encode($resp);
        echo $resp;
    }
}