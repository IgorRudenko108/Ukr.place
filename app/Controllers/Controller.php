<?php

namespace Controllers;

class Controller {

	protected $f3;
	protected $DB;
    protected $cookies;
    protected $region = 1;
    protected $myzodiac = 1;
    protected $justgood = false;

    protected $looked_news = [];

	function __construct() {
        $this->f3 = \Base::instance();
        $this->DB = new \DB\SQL(
            'mysql:host=' . $this->f3->get('db.hostname') . ';dbname=' . $this->f3->get('db.database'),
            $this->f3->get('db.username'),
            $this->f3->get('db.password')
        );
        $this->f3->set('current_route', $this->f3->hive()["PATH"]); //CURRENT ROUTE
        $this->cookies = $this->f3->get('COOKIE');
        if(isset($this->cookies["region"]))
        {
            $this->region = (int)$this->cookies["region"];
        }
        if(isset($this->cookies["zodiac"]))
        {
            $this->myzodiac = (int)$this->cookies["zodiac"];
        }
        if(isset($this->cookies["justgood"]))
        {
            $this->justgood = true;
        }
        if(isset($this->cookies["looked_news"]))
        {
            $this->looked_news = explode(',',$this->cookies["looked_news"]);
        }
	}

}

?>