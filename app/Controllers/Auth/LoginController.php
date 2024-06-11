<?php

namespace Controllers\Auth;

use Controllers\Controller;
use Helpers\RegionsHelper;
use Helpers\WeatherHelper;

class LoginController extends Controller {

    public function loginAction($f3, $params)
    {
        if($this->f3->exists('SESSION.logged_in'))
        {
            $this->f3->reroute('/');
        }
        $f3->set('page_title', 'Авторизація');

        echo \View::instance()->render('templates/auth/login.htm');
    }

    public function logoutAction($f3, $params)
    {
        $this->f3->clear('SESSION');
        $this->f3->reroute('/login');
    }

    public function loginActionPost($f3, $params)
    {
        $email = $f3->get('POST.email');
        $password = $f3->get('POST.password');

        //Check password in DB

        $user = $this->DB->exec("SELECT * FROM `users` WHERE `email` = :email AND `role` = 0",
            [
                ":email" => $email
            ]
        );

        if(count($user) > 0)
        {
            $verify = password_verify($password, $user[0]['password']);
            if($verify)
            {
                $this->f3->set('SESSION.user_id', $user[0]['id']);
                $this->f3->set('SESSION.username', $user[0]['name']);
                $this->f3->set('SESSION.surname', $user[0]['surname']);
                $this->f3->set('SESSION.logged_in', true);
                $this->f3->set('SESSION.type', $user[0]['role'] == 0);
                $this->f3->set('SESSION.avatar', $user[0]['avatar']);
                $this->f3->reroute('/');
            } else {
                $this->f3->set('err', "Невірний логін або пароль");
                echo \View::instance()->render('templates/auth/login.htm');
            }

        } else {
            $this->f3->set('err', "Невірний логін або пароль");
            echo \View::instance()->render('templates/auth/login.htm');
        }
    }
}