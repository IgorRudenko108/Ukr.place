<?php

namespace Controllers\Admin;

use Controllers\Admin\DefaultController;

class PagesController extends DefaultController
{
    public function pagesAction()
    {
        $this->f3->set('ONERROR', function($f3) {
            echo $this->f3->get('ERROR.text');
        });
        $pages_db = new \DB\SQL\Mapper($this->DB, 'pages');

        $limit = 10;
        $page = \Pagination::findCurrentPage();
        $subset = $pages_db->paginate($page-1, $limit, null, null);

        // build page links
        $pages = new \Pagination($subset['total'], $subset['limit']);
        // add some configuration if needed
        $pages->setTemplate('templates/admin/layout/pagebrowser.htm');
        // for template usage, serve generated pagebrowser to the hive
        $this->f3->set('pagebrowser', $pages->serve());

        $this->f3->set('template', 'pages/index.htm');
        $this->f3->set('pages', $subset);
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function pagesAddAction()
    {
        $this->f3->set('template', 'pages/add.htm');
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function pagesSaveAction($f3, $params)
    {
        $err = [];
        $name = $f3->get('POST.name');
        $alias = $f3->get('POST.alias');

        $check = $this->DB->exec("SELECT COUNT(*) AS count FROM `pages` WHERE `alias` LIKE '$alias'");
        if($check[0]["count"] > 0)
        {
            $err[] = "Такий alias вже існує.";
            //$f3->reroute($f3->get('SERVER.HTTP_REFERER'));
        }
        if(count($err) > 0)
        {
            $this->f3->set('errors', $err);
            $this->f3->set('template', 'pages/add.htm');
            echo \View::instance()->render('templates/admin/layout/app.htm');
        } else {

            $description_seo = $f3->get('POST.description_seo');
            $html = $f3->get('POST.html');

            $page = new \DB\SQL\Mapper($this->DB, 'pages');

            $page->name = $name;
            $page->alias = $alias;
            $page->description_seo = $description_seo;
            $page->html = $html;
            $page->save();

            $_SESSION["alert"] = "Сторінка успішно створена.";
        }

        $f3->reroute('/admin/pages');
    }

    public function pageActionShow($f3, $params)
    {
        $pageid = $params["pageid"];
        $page = $this->DB->exec("SELECT * FROM `pages` WHERE `id` LIKE :id",
            [
                ":id" => $pageid
            ]);
        $page = $page[0];
        $this->f3->set('template', 'pages/edit.htm');
        $this->f3->set('page', $page);
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function pageEditSave($f3, $params)
    {
        $err = [];
        $pageid = $params["pageid"];

        $name = $f3->get('POST.name');
        $slug = $f3->get('POST.slug');

        $check = $this->DB->exec("SELECT id FROM `pages` WHERE `alias` LIKE '$slug'");
        if($check)
        {
            $check_id = $check[0]["id"];
            if($check_id != $pageid)
            {
                $err[] = "Такий alias вже існує";
            }
        }

        if(count($err) > 0)
        {
            $this->f3->set('errors', $err);
            $this->f3->set('template', 'pages/add.htm');
            echo \View::instance()->render('templates/admin/layout/app.htm');
        } else {
            $description_seo = $f3->get('POST.description_seo');
            $html = $f3->get('POST.html');

            $page = new \DB\SQL\Mapper($this->DB, 'pages');
            $page->load(array('id=?', $pageid));

            $page->name = $name;
            $page->alias = $slug;
            $page->description_seo = $description_seo;
            $page->html = $html;
            $page->save();

            $_SESSION["alert"] = "Сторінка успішно збережена.";
        }
        $f3->reroute('/admin/pages');
    }

    public function pageDelete($f3, $params)
    {
        $pageid = $params["pageid"];
        $page = new \DB\SQL\Mapper($this->DB,'pages');
        $page->load(array('id=?', $pageid));
        $page->erase();
        $_SESSION["alert"] = "Сторінка успішно видалена.";
        $f3->reroute('/admin/pages');
    }
}