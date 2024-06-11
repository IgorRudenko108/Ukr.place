<?php

namespace Controllers;

use Collator;
use Helpers\CurrencyHelper;

class QuotesController extends Controller {

    public function indexAction($f3, $params)
    {
        //SEO
        $f3->set('page_title', 'Котування - валюти, крипта, пальне');
        $f3->set('description_seo', 'Інформація про курси валют, криптовалют та ціни на пальне.');

        $f3->set('content', 'quotes.htm');

        echo \View::instance()->render('templates/quotes.htm');

    }
    
}

?>