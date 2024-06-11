<?php

namespace Controllers;
class PagesController extends Controller{

    public function pageAction($f3, $params)
    {
        //Проверяем существование запрашиваемого товара
        $page_isset = $this->DB->exec("SELECT COUNT(*) as count FROM pages WHERE `alias` = '".$params['alias']."'");

        if ($page_isset[0]['count'] < 1) {
            http_response_code(404);
            echo \Template::instance()->render('templates/errors/404.htm');
        }

        //Получаем данные по запрошенномй странице
        $query_page = $this->DB->exec('SELECT * FROM `pages` WHERE `alias` = "'. $params['alias'] .'"');
//print_r($query_page); exit;
        $f3->set('page', $query_page[0]);

        //SEO
        $f3->set('page_title', $query_page[0]['name']);
        $f3->set('description_seo', $query_page[0]['description_seo']); 

        $f3->set('content', 'static_page.htm');
        
        echo \View::instance()->render('templates/layout.htm');

    }
    
}

?>