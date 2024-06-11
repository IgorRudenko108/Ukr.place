<?php

namespace Controllers\Admin;
use Controllers\Controller;

class DefaultController extends Controller{

    function beforeroute() 
    {
        if($this->f3->hive()["PATH"] != "/admin/login")
        {
            if($this->f3->get('SESSION.logged_in') !== NULL)
            {
                if($this->f3->get('SESSION.logged_in') == 0)
                {
                    $this->f3->clear('SESSION');
                    $this->f3->reroute('/admin/login');
                }
            } else {
                $this->f3->clear('SESSION');
                $this->f3->reroute('/admin/login');
            }
        }
    }

    function afterroute()
    {
		
	}
}