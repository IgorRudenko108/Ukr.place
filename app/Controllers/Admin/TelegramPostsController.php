<?php

namespace Controllers\Admin;

use Controllers\Admin\DefaultController;
use Helpers\TelegramHelper;

class TelegramPostsController extends DefaultController
{
    public function indexAction($f3, $params)
    {

        $pages_db = new \DB\SQL\Mapper($this->DB, 'tg_posts');

        $limit = 10;
        $page = \Pagination::findCurrentPage();
        $option = array('order' => 'id DESC');
        if($f3->exists('GET.source_id'))
        {
            $sourceID = $f3->get('GET.source_id');
            $subset = $pages_db->paginate($page-1, $limit, ['source_id = ?', $sourceID], $option);
        } else {
            $subset = $pages_db->paginate($page-1, $limit, null, $option);
        }

        foreach ($subset["subset"] as $key => $sub)
        {
            $sourceID = $sub["source_id"];
            $tg_channel = $this->DB->exec("SELECT * FROM `tg_sources` WHERE `id` = $sourceID");
            $subset["subset"][$key]["channel"] = $tg_channel[0];
            $subset["subset"][$key]["full_text"] = mb_substr( $sub["full_text"], 0, 64) . "...";
        }

        // build page links
        $pages = new \Pagination($subset['total'], $subset['limit']);
        $pages->setRange(5);
        // add some configuration if needed
        $pages->setTemplate('templates/admin/layout/pagebrowser.htm');
        $this->f3->set('pagebrowser', $pages->serve());

        $this->f3->set('template', 'telegram/posts/index.htm');
        $this->f3->set('posts', $subset);
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function deleteAction($f3, $params)
    {
        $channel_id = $params["id"];

        $channel = new \DB\SQL\Mapper($this->DB, 'tg_posts');
        $channel->load(array('id=?', $channel_id));
        $channel->erase();

        $_SESSION["alert"] = "Telegram пост успішно видалений.";
        header("Location: /admin/telegram-posts");
    }

    public function editAction($f3, $params)
    {
        $id = $params["id"];
        $post = new \DB\SQL\Mapper($this->DB, 'tg_posts');
        $post->load(array('id=?', $id));

        $post["full_text"] = str_replace("\n", "<br>", $post["full_text"]);

        $this->f3->set('post', $post);
        $this->f3->set('template', 'telegram/posts/edit.htm');
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function editActionPost($f3, $params)
    {
        $id = $params["id"];

        $full_text = $f3->get('POST.full_text');

        $this->DB->exec('SET NAMES utf8mb4');
        $channel = new \DB\SQL\Mapper($this->DB, 'tg_posts');
        $channel->load(array('id=?', $id));

        $channel->full_text = $full_text;
        $channel->update();

        $_SESSION["alert"] = "Telegram пост успішно сбережено.";
        header("Location: /admin/telegram-posts");
    }
}