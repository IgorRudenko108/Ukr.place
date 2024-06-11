<?php

namespace Controllers\Auth;

use Controllers\Controller;
use Helpers\RegionsHelper;

class RegisterController extends Controller {

    public function registerAction($f3, $params)
    {
        if($this->f3->exists('SESSION.logged_in'))
        {
            $this->f3->reroute('/');
        }

        $f3->set('page_title', 'Реестрація');

        $f3->set('regions', RegionsHelper::getRegions());
        echo \View::instance()->render('templates/auth/register.htm');
    }

    public function registerActionPost($f3, $params)
    {
        $err = [];

        $name = $f3->get('POST.name');
        $surname = $f3->get('POST.surname');
        $email = $f3->get('POST.email');
        $password = $f3->get('POST.password');
        $password_repeat = $f3->get('POST.password_repeat');

        if(strlen($email) < 5)
        {
            $err[] = "Email не може бути менше 5 символів.";
        }

        if(strlen($name) < 3)
        {
            $err[] = "Ім'я не може бути менше 3 символів.";
        }

        if(strlen($surname) < 3)
        {
            $err[] = "Прізвище не може бути менше 5 символів.";
        }

        if(strlen($password) < 8)
        {
            $err[] = "Пароль має бути мінімум 8 символів.";
        }

        //CHECK Password
        if($password != $password_repeat)
        {
            $err[] = "Паролі не співпадають.";
        }
        //CHECK Email
        $check = $this->DB->exec("SELECT COUNT(*) AS count FROM `users` WHERE `email` LIKE '$email'");
        if($check[0]["count"] > 0)
        {
            $err[] = "Користувач с таким email вже існує.";
        }

        if(count($err) > 0)
        {
            $this->f3->set('errors', $err);
            echo \View::instance()->render('templates/auth/register.htm');
        } else {

            $user = new \DB\SQL\Mapper($this->DB,'users');

            $user->email = $email;
            $user->password = password_hash($password, PASSWORD_DEFAULT);
            $user->role = 0;
            $user->name = $name;
            $user->surname = $surname;
            $user->save();

            $this->f3->set('SESSION.user_id', $user->id);
            $this->f3->set('SESSION.username', $name);
            $this->f3->set('SESSION.surname', $surname);
            $this->f3->set('SESSION.logged_in', true);
            $this->f3->set('SESSION.type', 0);
            $this->f3->set('SESSION.avatar', '/assets/img/noava.png');

            $f3->reroute('/');
        }
    }
}