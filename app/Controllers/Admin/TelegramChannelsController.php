<?php

namespace Controllers\Admin;

use Controllers\Admin\DefaultController;
use Helpers\TelegramHelper;

class TelegramChannelsController extends DefaultController
{
    public function indexAction($f3, $params)
    {

        $pages_db = new \DB\SQL\Mapper($this->DB, 'tg_sources');

        $limit = 10;
        $page = \Pagination::findCurrentPage();
        $filter = array('status <> ?', 3);
        $option = array('order' => 'id DESC');
        $subset = $pages_db->paginate($page-1, $limit, null, $option);

        // build page links
        $pages = new \Pagination($subset['total'], $subset['limit']);
        // add some configuration if needed
        $pages->setTemplate('templates/admin/layout/pagebrowser.htm');
        $this->f3->set('pagebrowser', $pages->serve());

        $this->f3->set('template', 'telegram/channels/index.htm');
        $this->f3->set('channels', $subset);
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function addAction($f3, $params)
    {
        $this->f3->set('template', 'telegram/channels/add.htm');
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function ajaxLoadChannel($f3, $params)
    {
        $channelLink = $f3->get('GET.channelLink');
        $channel = TelegramHelper::getChannelInfo($channelLink);
        $channel = json_encode($channel);
        header("Content-type: application/json");
        echo $channel;
    }

    public function addActionSave($f3, $params)
    {
        $account_link = $f3->get('POST.account_link');
        $name = $f3->get('POST.name');
        $description = $f3->get('POST.description');
        $image = $f3->get('POST.image');
        $position = $f3->get('POST.position');
        $active = $f3->exists('POST.active') ? 1 : 0;

        $account_link = explode("/", $account_link);
        $account_link = end($account_link);
        $account_link = trim($account_link);

        /* Download channel image */
        $data = file_get_contents($image);
        $save_to = $f3->GET('IMG_DIR') . "/telegram/".$account_link.".jpg";
        file_put_contents($save_to, $data);
        $image = "/assets/img/telegram/".$account_link.".jpg";

        $this->DB->exec('SET NAMES utf8mb4');
        $channel = new \DB\SQL\Mapper($this->DB, 'tg_sources');
        $channel->account_link = $account_link;
        $channel->name = $name;
        $channel->description = $description;
        $channel->image = $image;
        $channel->position = $position;
        $channel->active = $active;
        $channel->save();

        $_SESSION["alert"] = "Канал успішно додано.";
        header("Location: /admin/telegram-channels");
    }

    public function deleteAction($f3, $params)
    {
        $channel_id = $params["id"];

        $channel = new \DB\SQL\Mapper($this->DB, 'tg_sources');
        $channel->load(array('id=?', $channel_id));
        $channel->erase();

        $_SESSION["alert"] = "Telegram канал успішно видалений.";
        header("Location: /admin/telegram-channels");
    }

    public function editAction($f3, $params)
    {
        $id = $params["id"];
        $channel = new \DB\SQL\Mapper($this->DB, 'tg_sources');
        $channel->load(array('id=?', $id));

        $this->f3->set('channel', $channel);
        $this->f3->set('template', 'telegram/channels/edit.htm');
        echo \View::instance()->render('templates/admin/layout/app.htm');
    }

    public function editActionPost($f3, $params)
    {
        $id = $params["id"];

        $account_link = $f3->get('POST.account_link');
        $name = $f3->get('POST.name');
        $description = $f3->get('POST.description');
        $image = $f3->get('POST.image');
        $position = $f3->get('POST.position');
        $active = $f3->exists('POST.active') ? 1 : 0;

        $account_link = explode("/", $account_link);
        $account_link = end($account_link);
        $account_link = trim($account_link);

        /* Download channel image */
        if(!str_contains($image,"assets"))
        {
            $data = file_get_contents($image);
            $save_to = $f3->GET('IMG_DIR') . "/telegram/".$account_link.".jpg";
            file_put_contents($save_to, $data);
            $image = "/assets/img/telegram/".$account_link.".jpg";
        }

        $this->DB->exec('SET NAMES utf8mb4');
        $channel = new \DB\SQL\Mapper($this->DB, 'tg_sources');
        $channel->load(array('id=?', $id));

        $channel->account_link = $account_link;
        $channel->name = $name;
        $channel->description = $description;
        $channel->image = $image;
        $channel->position = $position;
        $channel->active = $active;
        $channel->update();

        $_SESSION["alert"] = "Канал успішно сбережено.";
        header("Location: /admin/telegram-channels");
    }

    public function setActive($f3, $params)
    {
        $id = (int)$f3->get('POST.id');
        $checked = (int)$f3->get('POST.checked');

        $channel = new \DB\SQL\Mapper($this->DB, 'tg_sources');
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