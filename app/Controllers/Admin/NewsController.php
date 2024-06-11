<?php

namespace Controllers\Admin;

use Controllers\Admin\DefaultController;

class NewsController extends DefaultController
{
    public function indexAction($f3, $params)
    {
        $pages_db = new \DB\SQL\Mapper($this->DB, 'news');

        $limit = 50;
        $page = \Pagination::findCurrentPage();
        $filter = array('status <> ?', 3);
        $option = array('order' => 'id DESC');
        $subset = $pages_db->paginate($page-1, $limit, null, $option);

        $inChain = [];
        $generalInChain = [];

        $allIDS = [];

        foreach($subset["subset"] as $subNews)
        {
            $allIDS[] = $subNews["id"];
        }

        $allIDSText = implode(',', $allIDS);

        $get = $this->DB->exec("SELECT * FROM `duplicates` WHERE `original_news_id` IN($allIDSText) OR `duplicate_news_id` IN($allIDSText)");
        
        foreach($get as $num)
        {
            if(!in_array($num["original_news_id"], $generalInChain))
            {
                $generalInChain[] = $num["original_news_id"];
            }
            if(!in_array($num["duplicate_news_id"], $inChain))
            {
                $inChain[] = $num["duplicate_news_id"];
            }
        }

        $allCategories = $this->DB->exec("SELECT `id`, `name` FROM `categories`");
        
        $catList = [];
        foreach($allCategories as $cat)
        {
            $catList[$cat["id"]] = $cat["name"];
        }


        $lastTenDuplicates = $this->DB->exec("SELECT DISTINCT `original_news_id` FROM `duplicates` ORDER BY `original_news_id` DESC LIMIT 10;");

        $duplicateList = [];
        foreach($lastTenDuplicates as $d)
        {
            $dnum = (string)$d["original_news_id"];
            $data = $this->DB->exec("SELECT `id`,`title` FROM `news` WHERE `id` = $dnum");

            $duplicateList[] = $data[0];
        }

        // build page links
        $pages = new \Pagination($subset['total'], $subset['limit']);
        $pages->setRange(5);
        // add some configuration if needed
        $pages->setTemplate('templates/admin/layout/pagebrowser.htm');
        // for template usage, serve generated pagebrowser to the hive
        $this->f3->set('pagebrowser', $pages->serve());

        $this->f3->set('template', 'news/index.htm');
        $this->f3->set('duplicateList', $duplicateList);
        $this->f3->set('generalInChain', $generalInChain);
        $this->f3->set('inChain', $inChain);
        $this->f3->set('catList', $catList);
        $this->f3->set('news', $subset);

        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function addAction($f3, $params)
    {
        $categories = new \DB\SQL\Mapper($this->DB, 'categories');
        $categories = $categories->find();
        $this->f3->set('categories', $categories);
        $this->f3->set('template', 'news/add.htm');
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function addActionPost($f3, $params)
    {
        $title = $f3->get('POST.title');
        $description = $f3->get('POST.description');
        $full_text = $f3->get('POST.full_text');
        $category_id  = $f3->get('POST.category_id');
        $img = $f3->get('POST.img');
        $source_link  = $f3->get('POST.source_link');
        $is_good = $f3->exists('POST.is_good') ? 1 : 0;

        $article = new \DB\SQL\Mapper($this->DB, 'news');
        $article->title = $title;
        $article->description = $description;
        $article->full_text = $full_text;
        $article->category_id = $category_id;
        $article->img = $img;
        $article->source_link = $source_link;
        $article->is_good = $is_good;

        $article->save();

        $_SESSION["alert"] = "Новина успішно додана.";
        header("Location: /admin/news");
    }

    public function deleteAction($f3, $params)
    {
        $id = $params['id'];
        $article = new \DB\SQL\Mapper($this->DB, 'news');
        $article->load(array('id=?', $id));

        $article->erase();

        $_SESSION["alert"] = "Новина успішно видалена.";
        header("Location: /admin/news");
    }

    public function editAction($f3, $params)
    {
        $id = $params["id"];
        $article = new \DB\SQL\Mapper($this->DB, 'news');
        $article->load(array('id=?', $id));

        $categories = new \DB\SQL\Mapper($this->DB, 'categories');
        $categories = $categories->find();
        $this->f3->set('categories', $categories);

        $this->f3->set('article', $article);
        $this->f3->set('template', 'news/edit.htm');
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function editActionPost($f3, $params)
    {
        $id = $params["id"];

        $title = $f3->get('POST.title');
        $description = $f3->get('POST.description');
        $full_text = $f3->get('POST.full_text');
        $category_id  = $f3->get('POST.category_id');
        $img = $f3->get('POST.img');
        $source_link  = $f3->get('POST.source_link');
        $is_good = $f3->exists('POST.is_good') ? 1 : 0;

        $article = new \DB\SQL\Mapper($this->DB, 'news');
        $article->load(array('id=?', $id));
        $article->title = $title;
        $article->description = $description;
        $article->full_text = $full_text;
        $article->category_id = $category_id;
        $article->img = $img;
        $article->source_link = $source_link;
        $article->is_good = $is_good;

        $article->update();

        $_SESSION["alert"] = "Новина успішно відредагована.";
        header("Location: /admin/news");
    }

    public function duplicateActionPost($f3, $params)
    {
        $ids = $f3->get('POST.id');

        $idsText = implode(',', $ids);
        
        $news = $this->DB->exec("SELECT `id` FROM `news` WHERE `id` IN ($idsText) ORDER BY `created_at` ASC");

        $sorted = [];
        foreach($news as $article)
        {
            $sorted[] = $article["id"];
        }

        for($i = 1; $i < count($sorted); $i++)
        {
            $duplicate = new \DB\SQL\Mapper($this->DB, 'duplicates');
            $duplicate->original_news_id = $sorted[0];
            $duplicate->duplicate_news_id = $sorted[$i];
            $duplicate->save();
        }

        $_SESSION["alert"] = "Дублювання успішно виставлено.";
        header("Location: /admin/news");
    }

    public function addToDuplicatedPost($f3, $params)
    {
        $originalID = $f3->get('POST.originalID');
        $articleID = $f3->get('POST.articalID');

        $duplicate = new \DB\SQL\Mapper($this->DB, 'duplicates');
        $duplicate->original_news_id = $originalID;
        $duplicate->duplicate_news_id = $articleID;
        $duplicate->save();
        $_SESSION["alert"] = "Новину успішно додано до ланцюга.";
        $f3->reroute($f3->get('SERVER.HTTP_REFERER'));
        //header("Location: /admin/news");
    }

    public function selectCollectionAjax($f3, $params)
    {
        $articleID = (int)$params['articleid'];
        $collections = $this->DB->exec("SELECT `id`,`name` FROM `collections`");

        $selectedCollections = $this->DB->exec("SELECT `collections_id` FROM `collections_news` WHERE `news_id` = $articleID");
        $selectedIDList = [];
        foreach ($selectedCollections as $selectedCollection) {
            $selectedIDList[] = $selectedCollection['collections_id'];
        }

        $html = "";
        foreach($collections as $cl)
        {
            if(in_array($cl['id'], $selectedIDList))
            {
                $html .= '<option value="'.$cl['id'].'" selected>'.$cl['name'].'</option>';
            } else {
                $html .= '<option value="'.$cl['id'].'">'.$cl['name'].'</option>';
            }

        }
        echo $html;
    }

    public function selected_collections($f3, $params)
    {

        $articleID = (int)$f3->get('POST.articalID');
        $selectedCollections = $f3->get('POST.collectionSelect');

        $this->DB->exec("DELETE FROM `collections_news` WHERE `news_id` = $articleID");

        if($selectedCollections) {
            foreach ($selectedCollections as $collectionID) {
                $this->DB->exec("INSERT INTO `collections_news`(`news_id`, `collections_id`) VALUES ('$articleID','$collectionID')");
            }
        }

        $_SESSION["alert"] = "Підбірки успішно встановлено.";
        $f3->reroute($f3->get('SERVER.HTTP_REFERER'));
        //header("Location: /admin/news");
    }

    public function setTopAction($f3, $params)
    {
        $id = $params['id'];
        $article = new \DB\SQL\Mapper($this->DB, 'news');
        $article->load(array('id=?', $id));

        $article->top = true;
        $article->update();

        $_SESSION["alert"] = "Новину додано до ТОП.";
        $f3->reroute($f3->get('SERVER.HTTP_REFERER'));
        //header("Location: /admin/news");
    }
}