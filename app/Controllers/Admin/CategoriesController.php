<?php

namespace Controllers\Admin;

use Controllers\Admin\DefaultController;
use Helpers\NewsHelper;

class CategoriesController extends DefaultController
{
	public function indexAction($f3, $params)
	{
		
        $pages_db = new \DB\SQL\Mapper($this->DB, 'categories');

        $limit = 50;
        $page = \Pagination::findCurrentPage();
        $filter = array('status <> ?', 3);
        $option = array('order' => 'id DESC');
        $subset = $pages_db->paginate($page-1, $limit, null, $option);

        // build page links
        $pages = new \Pagination($subset['total'], $subset['limit']);
        // add some configuration if needed
        $pages->setTemplate('templates/admin/layout/pagebrowser.htm');
        // for template usage, serve generated pagebrowser to the hive
        $this->f3->set('pagebrowser', $pages->serve());

        $this->f3->set('template', 'categories/index.htm');
        $this->f3->set('categories', $subset);

        echo \View::instance()->render('templates/admin/layout/app.htm');
	}

	public function addAction($f3, $params)
	{
        $this->f3->set('template', 'categories/add.htm');
        echo \View::instance()->render('templates/admin/layout/app.htm');
	}	

	public function addActionSave($f3, $params)
	{
		$name = $f3->get('POST.name');
		//$alias = $f3->get('POST.alias');

		$categorie = new \DB\SQL\Mapper($this->DB, 'categories');
        $categorie->name = $name;
        $categorie->alias = NewsHelper::generateAlias($name);
        $categorie->save();

        $_SESSION["alert"] = "Категорія успішно додана.";
        header("Location: /admin/categories");
	}

    public function deleteAction($f3, $params)
    {
        $id = $params['id'];
        $article = new \DB\SQL\Mapper($this->DB, 'categories');
        $article->load(array('id=?', $id));

        $article->erase();

        $_SESSION["alert"] = "Категорія успішно видалена.";
        header("Location: /admin/categories");
    }

    public function editAction($f3, $params)
    {
        $id = $params["id"];
        $categorie = new \DB\SQL\Mapper($this->DB, 'categories');
        $categorie->load(array('id=?', $id));

        $this->f3->set('categorie', $categorie);
        $this->f3->set('template', 'categories/edit.htm');
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function editActionPost($f3, $params)
    {
        $id = $params["id"];

        $name = $f3->get('POST.name');
        $alias = NewsHelper::generateAlias($name);

        $categorie = new \DB\SQL\Mapper($this->DB, 'categories');
        $categorie->load(array('id=?', $id));
        $categorie->name = $name;
        $categorie->alias = $alias;

        $categorie->update();

        $_SESSION["alert"] = "Категорія успішно відредагована.";
        header("Location: /admin/categories");
    }
}