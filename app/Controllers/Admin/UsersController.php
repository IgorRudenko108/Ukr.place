<?php

namespace Controllers\Admin;

use Controllers\Admin\DefaultController;
class UsersController extends DefaultController
{
    public function usersAction()
    {
        if($_SESSION["type"] != 1)
        {
            echo "Вы не имеете доступа к этой странице";
            exit;
        }
        $categories = new \DB\SQL\Mapper($this->DB, 'users');

        $limit = 10;
        $page = \Pagination::findCurrentPage();
        $subset = $categories->paginate($page-1, $limit, null, null);

        // build page links
        $pages = new \Pagination($subset['total'], $subset['limit']);
        // add some configuration if needed
        $pages->setTemplate('templates/admin/layout/pagebrowser.htm');
        // for template usage, serve generated pagebrowser to the hive
        $this->f3->set('pagebrowser', $pages->serve());

        $this->f3->set('template', 'users/index.htm');
        $this->f3->set('users', $subset);
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function usersAddAction()
    {
        $err = [];
        if($_SESSION["type"] != 1)
        {
            $err[] = "Ви не маєте доступу до цієї сторінки.";
            $this->f3->set('errors', $err);
            $this->f3->set('template', 'users/add.htm');
            echo \View::instance()->render('templates/admin/layout/app.htm');
        }
        $this->f3->set('template', 'users/add.htm');
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function usersAddSaveAction($f3, $params)
    {
        $err = [];
        if($_SESSION["type"] != 1)
        {
            $err[] = "Ви не маєте доступу до цієї сторінки.";
        }

        $name = $f3->get('POST.name');
        $surname = $f3->get('POST.surname');
        $email = $f3->get('POST.email');
        $password = $f3->get('POST.password');
        $password_repeat = $f3->get('POST.password_repeat');
        $role = $f3->get('POST.role');
        $ava = "";

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
            } else {
                $err[] = "Невірний формат зображення.";
            }
        }

        if(count($err) > 0)
        {
            $this->f3->set('errors', $err);
            $this->f3->set('template', 'users/add.htm');
            echo \View::instance()->render('templates/admin/layout/app.htm');
        } else {

            $user = new \DB\SQL\Mapper($this->DB,'users');

            $user->email = $email;
            $user->password = password_hash($password, PASSWORD_DEFAULT);
            $user->role = (int)$role;
            $user->name = $name;
            $user->surname = $surname;
            if(!empty($ava))
            {
                $user->avatar = $ava;
            }
            $user->save();

            $_SESSION["alert"] = "Користувача успішно створено.";
            $f3->reroute('/admin/users');
        }
    }

    public function usersEditAction($f3, $params)
    {
        $userid = $params["userid"];
        $user = $this->DB->exec("SELECT * FROM `users` WHERE `id` LIKE :id",
            [
                ":id" => $userid
            ]);
        $user = $user[0];
        $this->f3->set('template', 'users/edit.htm');
        $this->f3->set('user', $user);
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function usersSaveAction($f3, $params)
    {
        $err = [];
        if($_SESSION["type"] != 1)
        {
            $err[] = "Ви не маєте доступу до цієї сторінки.";
        }

        $userid = $params["userid"];

        $name = $f3->get('POST.name');
        $surname = $f3->get('POST.surname');
        $email = $f3->get('POST.email');
        $password = $f3->get('POST.password');
        $password_repeat = $f3->get('POST.password_repeat');
        $role = $f3->get('POST.role');
        $ava = "";

        //CHECK Password
        if(!empty($password) && !empty($password_repeat))
        {
            if($password != $password_repeat)
            {
                $err[] = "Паролі не співпадають";
            }
        }
        //CHECK Email
        $check = $this->DB->exec("SELECT id FROM `users` WHERE `email` LIKE '$email'");
        if($check)
        {
            $check_id = $check[0]["id"];
            if($check_id != $userid)
            {
                $err[] = "Користувач с таким email вже існує.";
            }
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
            } else {
                $err[] = "Невірний формат зображення.";
            }
        }

        $user = new \DB\SQL\Mapper($this->DB, 'users');
        $user->load(array('id=?', $userid));

        if(count($err) > 0)
        {
            $this->f3->set('user', $user);
            $this->f3->set('errors', $err);
            $this->f3->set('template', 'users/edit.htm');
            echo \View::instance()->render('templates/admin/layout/app.htm');
        } else {

            $user->name = $name;
            $user->surname = $surname;
            $user->email = $email;
            if(!empty($ava))
            {
                $user->avatar = $ava;
            }

            if (!empty($password) && !empty($password_repeat)) {
                $user->password = password_hash($password, PASSWORD_DEFAULT);
            }

            $user->role = $role;
            $user->save();
            $_SESSION["alert"] = "Успішно сбережено.";
            $f3->reroute('/admin/users');
        }
    }

    public function usersDeleteAction($f3, $params)
    {
        if($_SESSION["type"] != 1)
        {
            $f3->set('SESSION.err', 'Ви не маєте доступу до цієї дії.');
            $f3->reroute($f3->get('SERVER.HTTP_REFERER'));
            exit;
        }
        $userid = $params["userid"];
        if($userid == 1)
        {
            $f3->set('SESSION.err', 'Ви не можете видалити головного користувача.');
            $f3->reroute($f3->get('SERVER.HTTP_REFERER'));
        } else {
            $categorie = new \DB\SQL\Mapper($this->DB,'users');
            $categorie->load(array('id=?', $userid));
            $categorie->erase();
            $_SESSION["alert"] = "Користувача успішно видалено.";
            $f3->reroute('/admin/users');
        }
    }
}