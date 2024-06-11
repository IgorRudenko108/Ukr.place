<?php

namespace Controllers\Admin;

use Controllers\Admin\DefaultController;
use Helpers\NewsHelper;

class CollectionsController extends DefaultController
{
    public function indexAction($f3, $params)
    {
        $pages_db = new \DB\SQL\Mapper($this->DB, 'collections');

        $limit = 50;
        $page = \Pagination::findCurrentPage();
        $filter = array('status <> ?', 3);
        $option = array('order' => 'id DESC');
        $subset = $pages_db->paginate($page-1, $limit, null, $option);

     	foreach($subset['subset'] as $key => $sub)
     	{
            $collID = (int)$sub["id"];
            $count = $this->DB->exec("SELECT COUNT(*) AS `count` FROM `collections_news` WHERE `collections_id` = $collID");
            $subset['subset'][$key]['news_count'] = $count[0]["count"];
     	}
        // build page links
        $pages = new \Pagination($subset['total'], $subset['limit']);
        // add some configuration if needed
        $pages->setTemplate('templates/admin/layout/pagebrowser.htm');
        // for template usage, serve generated pagebrowser to the hive
        $this->f3->set('pagebrowser', $pages->serve());

        $this->f3->set('template', 'collections/index.htm');
        $this->f3->set('collections', $subset);

        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function addAction($f3, $params)
    {
        $categories = new \DB\SQL\Mapper($this->DB, 'categories');
        $categories = $categories->find();
        $this->f3->set('categories', $categories);
        $this->f3->set('template', 'collections/add.htm');
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function addActionPost($f3, $params)
    {
        $err = [];

        $name = $f3->get('POST.name');
        $active = (int)$f3->get('POST.active');
        $alias = NewsHelper::generateAlias($name);

        $logo = $f3->get('FILES.logo');

        $allowed_types = [
            "image/jpeg",
            "image/png"
        ];

        if(!empty($logo["name"]))
        {
            if(in_array($logo["type"], $allowed_types))
            {
                $ext = explode(".", $logo["name"]);
                $newname = md5($logo["name"] . time());
                $newname = substr($newname, 0, 10);
                $newname = $newname . "." . end($ext);
                $logo_file_name = "/assets/img/collections/$newname";
                move_uploaded_file($logo["tmp_name"], __DIR__ . "/../../..".$logo_file_name);
            } else {
                $err[] = "Невірний формат зображення.";
            }
        }

        if(count($err) > 0)
        {
            $this->f3->set('errors', $err);
            $this->f3->set('template', 'partners/add.htm');
            echo \View::instance()->render('templates/admin/layout/app.htm');
        } else {

            $collection = new \DB\SQL\Mapper($this->DB, 'collections');
            $collection->name = $name;
            $collection->alias = $alias;
            $collection->active = $active;

            if(isset($logo_file_name))
            {
                $collection->logo = $logo_file_name;
            }

            $collection->save();

            $_SESSION["alert"] = "Колекція успішно створена.";
            header("Location: /admin/collections");
        }
    }

    public function deleteAction($f3, $params)
    {
        $id = $params['id'];
        $article = new \DB\SQL\Mapper($this->DB, 'collections');
        $article->load(array('id=?', $id));

        $article->erase();

        $_SESSION["alert"] = "Колекція успішно видалена.";
        header("Location: /admin/collections");
    }

    public function editAction($f3, $params)
    {
        $id = $params["id"];
        $collection = new \DB\SQL\Mapper($this->DB, 'collections');
        $collection->load(array('id=?', $id));

        $this->f3->set('collection', $collection);
        $this->f3->set('template', 'collections/edit.htm');
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function editActionPost($f3, $params)
    {
        $err = [];

        $id = $params["id"];

        $name = $f3->get('POST.name');
        $active = $f3->get('POST.active');
        $alias = NewsHelper::generateAlias($name);

        $logo = $f3->get('FILES.logo');

        $allowed_types = [
            "image/jpeg",
            "image/png"
        ];

        if(!empty($logo["name"]))
        {
            if(in_array($logo["type"], $allowed_types))
            {
                $ext = explode(".", $logo["name"]);
                $newname = md5($logo["name"] . time());
                $newname = substr($newname, 0, 10);
                $newname = $newname . "." . end($ext);
                $logo_file_name = "/assets/img/collections/$newname";
                move_uploaded_file($logo["tmp_name"], __DIR__ . "/../../..".$logo_file_name);
            } else {
                $err[] = "Невірний формат зображення.";
            }
        }

        if(count($err) > 0)
        {
            $this->f3->set('errors', $err);
            $this->f3->set('template', 'partners/add.htm');
            echo \View::instance()->render('templates/admin/layout/app.htm');
        } else {
            $collection = new \DB\SQL\Mapper($this->DB, 'collections');
            $collection->load(array('id=?', $id));
            $collection->name = $name;
            $collection->active = $active;
            $collection->alias = $alias;

            if(isset($logo_file_name))
            {
                $collection->logo = $logo_file_name;
            }

            $collection->update();

            $_SESSION["alert"] = "Колекція успішно відредагована.";
            header("Location: /admin/collections");
        }
    }

    public function setActive($f3, $params)
    {
        $id = (int)$f3->get('POST.id');
        $checked = (int)$f3->get('POST.checked');

        $channel = new \DB\SQL\Mapper($this->DB, 'collections');
        $channel->load(array('id=?', $id));
        $channel->active = $checked;
        $channel->update();
        $data = [
            "success" => true,
            "active" => (bool)$checked
        ];
        header("Content-type: application/json");
        echo json_encode($data);
    }
}