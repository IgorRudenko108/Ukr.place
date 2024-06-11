<?php

namespace Controllers\Admin;

use Controllers\Admin\DefaultController;

class AdminController extends DefaultController{

    public function indexAction()
    {
        $newsCount = $this->DB->exec("SELECT COUNT(*) AS count FROM `news`");
        $newsCount = $newsCount[0]["count"];

        $categoriesCount = $this->DB->exec("SELECT COUNT(*) AS count FROM `categories`");
        $categoriesCount = $categoriesCount[0]["count"];

        $partnersCount = $this->DB->exec("SELECT COUNT(*) AS count FROM `partners`");
        $partnersCount = $partnersCount[0]["count"];

        $telegramPostCount = $this->DB->exec("SELECT COUNT(*) AS count FROM `tg_posts`");
        $telegramPostCount = $telegramPostCount[0]["count"];

        $usersCount = $this->DB->exec("SELECT COUNT(*) AS count FROM `users`");
        $usersCount = $usersCount[0]["count"];

        $pagesCount = $this->DB->exec("SELECT COUNT(*) AS count FROM `pages`");
        $pagesCount = $pagesCount[0]["count"];

        $telegramChannelsCount = $this->DB->exec("SELECT COUNT(*) AS count FROM `tg_sources`");
        $telegramChannelsCount = $telegramChannelsCount[0]["count"];

        $paymentsCount = $this->DB->exec("SELECT COUNT(*) AS count FROM `payments`");
        $paymentsCount = $paymentsCount[0]["count"];

        $this->f3->set('newsCount', $newsCount);
        $this->f3->set('categoriesCount', $categoriesCount);
        $this->f3->set('partnersCount', $partnersCount);
        $this->f3->set('telegramPostCount', $telegramPostCount);

        $this->f3->set('usersCount', $usersCount);
        $this->f3->set('pagesCount', $pagesCount);
        $this->f3->set('telegramChannelsCount', $telegramChannelsCount);
        $this->f3->set('paymentsCount', $paymentsCount);

        $this->f3->set('template', 'index.htm');
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function loginAction()
    {
        echo \View::instance()->render('templates/admin/login.htm');
    }

    public function loginPostAction()
    {
        $email = $this->f3->get('POST.email');
        $password = $this->f3->get('POST.password');
        $user = $this->DB->exec("SELECT * FROM `users` WHERE `email` LIKE :email",
            [
                ":email" => $email,
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
                $this->f3->set('SESSION.type', $user[0]['role'] == 1);
                $this->f3->set('SESSION.avatar', $user[0]['avatar']);
                $this->f3->reroute('/admin');
            } else {
                $this->f3->set('err', "Невірний логін або пароль");
                echo \View::instance()->render('templates/admin/login.htm');
            }

        } else {
            $this->f3->set('err', "Невірний логін або пароль");
            echo \View::instance()->render('templates/admin/login.htm');
        }
    }

    public function logout()
	{
		$this->f3->clear('SESSION');
		$this->f3->reroute('/admin/login');
	}

    public function profileAction($f3, $params)
    {
        $userID = $f3->GET('SESSION.user_id');

        $user = new \DB\SQL\Mapper($this->DB, 'users');
        $user->load(array('id=?', $userID));

        $this->f3->set('user', $user);
        $this->f3->set('template', 'profile/profile.htm');
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function profileActionSave($f3, $params)
    {
        $err = [];

        $userID = $f3->GET('SESSION.user_id');

        $name = $f3->get('POST.name');
        $surname = $f3->get('POST.surname');
        $email = $f3->get('POST.email');
        $current_password = $f3->get('POST.current_password');
        $new_password = $f3->get('POST.new_password');
        $new_password_repeat = $f3->get('POST.new_password_repeat');

        //CHECK Password
        $pass_verify = false;
        if(!empty($new_password) || !empty($new_password_repeat)) {
            if ($new_password != $new_password_repeat) {
                $err[] = "Паролі не співпадають.";
            } else {
                //Check current password
                $db_password = $this->DB->exec("SELECT `password` FROM `users` WHERE `id` = $userID");
                $db_password = $db_password[0]["password"];

                $pass_verify = password_verify($current_password, $db_password);
                if (!$pass_verify) {
                    $err[] = "Поточний пароль, не вірний.";
                }
            }
        }
        //CHECK Email
        $check = $this->DB->exec("SELECT COUNT(*) AS count FROM `users` WHERE `email` LIKE '$email' AND `id` != $userID;");
        if($check[0]["count"] > 0)
        {
            $err[] = "Користувач с таким email вже існує.";
        }

        $avatar = $f3->get('FILES.avatar');
        if(!empty($avatar["name"]))
        {
            $allowed_types = [
                "image/jpeg",
                "image/png"
            ];

            if(in_array($avatar["type"], $allowed_types))
            {
                $ext = explode(".", $avatar["name"]);
                $newname = md5($avatar["name"] . time());
                $newname = substr($newname, 0, 10);
                $newname = $newname . "." . end($ext);
                $ava = "/assets/img/avatars/$newname";
                move_uploaded_file($avatar["tmp_name"], __DIR__ . "/../../..".$ava);
                $f3->set('SESSION.avatar', $ava);
            } else {
                $err[] = "Невірний формат зображення.";
            }
        }

        if(count($err) > 0)
        {
            $user = new \DB\SQL\Mapper($this->DB, 'users');
            $user->load(array('id=?', $userID));

            $this->f3->set('user', $user);
            $this->f3->set('errors', $err);
            $this->f3->set('template', 'profile/profile.htm');
            echo \View::instance()->render('templates/admin/layout/app.htm');
        } else {

            $user = new \DB\SQL\Mapper($this->DB, 'users');
            $user->load(array('id=?', $userID));

            $user->email = $email;
            if($new_password == $new_password_repeat && $pass_verify)
            {
                $user->password = password_hash($new_password, PASSWORD_DEFAULT);
            }
            $user->name = $name;
            $user->surname = $surname;
            if(!empty($ava))
            {
                $user->avatar = $ava;
            }
            $user->update();

            $f3->set('SESSION.username', $name);
            $f3->set('SESSION.surname', $surname);

            $_SESSION["alert"] = "Дані успішно збережені.";
            $f3->reroute('/admin/profile');
        }
    }

}

?>