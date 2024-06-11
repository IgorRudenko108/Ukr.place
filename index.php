<?php

if(!isset($argv[1])) {
    session_start();
}

require_once('vendor/autoload.php');
require_once('app/functions/custom_functions.php');

date_default_timezone_set('Europe/Kiev');

$f3 = Base::instance();
$f3->set('AUTOLOAD','app/');
$f3->set('CACHE', TRUE);

$f3->set('DEBUG', 3);

$f3->set('MAIN_DIR', __DIR__);
$f3->set('IMG_DIR', __DIR__ . '/assets/img');

$f3->config('config.ini');


$f3->set('ONERROR', function($f3){
	if($f3->get('ERROR.code') == 404) {
		echo \Template::instance()->render('templates/errors/404.htm');
	} else {
		echo $f3->get('ERROR.text');
	}
});

if(isset($argv[1]))
{
    $cron = new Cron\Cron();
    $command = $argv[1];
    $cron->$command();
    exit;
}

$f3->route('GET /','Controllers\MainController->indexAction');
$f3->route('GET /news/@alias','Controllers\NewsController->articleByAlias');
$f3->route('GET /news/@alias/gopartner','Controllers\NewsController->goPartner');
$f3->route('GET /category/@cat_alias','Controllers\CategoryController->indexAction');
$f3->route('GET /region/@reg_id','Controllers\RegionController->indexAction');
$f3->route('GET /collection/@collection_alias','Controllers\CollectionsController->indexAction');
$f3->route('GET /quotes','Controllers\QuotesController->indexAction');
$f3->route('POST /save','Controllers\MainController->save');
$f3->route('POST /search','Controllers\SearchController->indexAction');
$f3->route('POST /article_like','Controllers\NewsController->articleLike');

### Register
$f3->route('GET /register', 'Controllers\Auth\RegisterController->registerAction');
$f3->route('POST /register', 'Controllers\Auth\RegisterController->registerActionPost');
## Auth
$f3->route('GET /login', 'Controllers\Auth\LoginController->loginAction');
$f3->route('POST /login', 'Controllers\Auth\LoginController->loginActionPost');
### Logout
$f3->route('GET /logout', 'Controllers\Auth\LoginController->logoutAction');

$f3->route('GET /api/news/search','Controllers\NewsController->apiSearch');
$f3->route('GET /news/load','Controllers\NewsController->loadMoreNews');
$f3->route('POST /set-region','Controllers\MainController->setRegion');
$f3->route('POST /set-horoscope','Controllers\MainController->setZodiac');
$f3->route('POST /set-just-good','Controllers\MainController->setJustGood');

$f3->route('GET /archive','Controllers\ArchiveController->indexAction');
$f3->route('GET /personal','Controllers\PersonalController->indexAction');
$f3->route('GET /load-telegram-post','Controllers\MainController->loadTelegramPost');


### Admin routes
$f3->route('GET /admin','Controllers\Admin\AdminController->indexAction');
### Admin auth
$f3->route('GET /admin/login','Controllers\Admin\AdminController->loginAction');
$f3->route('POST /admin/login','Controllers\Admin\AdminController->loginPostAction');
$f3->route('GET /admin/logout','Controllers\Admin\AdminController->logout');
### Admin Charts
$f3->route('GET /admin/chars/@table/last_month','Controllers\Admin\ChartsController->getCountByLastMonth');
$f3->route('GET /admin/chars/percentage_good_news','Controllers\Admin\ChartsController->percentage_good_news');
$f3->route('GET /admin/chars/percentage_regions_news','Controllers\Admin\ChartsController->percentage_regions_news');
### Admin Partners
$f3->route('GET /admin/partners','Controllers\Admin\PartnersController->indexAction');
$f3->route('GET /admin/partners/@page','Controllers\Admin\PartnersController->indexAction');
$f3->route('GET /admin/partners/add','Controllers\Admin\PartnersController->addAction');
$f3->route('POST /admin/partners/add','Controllers\Admin\PartnersController->addActionPost');
$f3->route('GET /admin/partners/@id/edit','Controllers\Admin\PartnersController->editAction');
$f3->route('POST /admin/partners/@id/edit','Controllers\Admin\PartnersController->editActionPost');
$f3->route('GET /admin/partners/@id/delete','Controllers\Admin\PartnersController->deleteAction');
$f3->route('GET /admin/partners/@id/rss','Controllers\Admin\PartnersController->rssAction');
$f3->route('GET /admin/partners/@id/rss/add','Controllers\Admin\PartnersController->rssAddAction');
$f3->route('POST /admin/partners/@id/rss/add','Controllers\Admin\PartnersController->addRssPost');
$f3->route('POST /admin/partners/rss/check','Controllers\Admin\PartnersController->checkRss');
$f3->route('POST /admin/partners/rss/parse','Controllers\Admin\PartnersController->parseRss');
$f3->route('GET /admin/partners/@id/rss/@rssid/delete','Controllers\Admin\PartnersController->deleteRss');
$f3->route('GET /admin/partners/@id/rss/@rssid/edit','Controllers\Admin\PartnersController->editRssAction');
$f3->route('POST /admin/partners/@id/rss/@rssid/edit','Controllers\Admin\PartnersController->editRssSaveAction');
$f3->route('GET /admin/partners/@id/stat','Controllers\Admin\PartnersController->partnerStat');
$f3->route('POST /admin/partners/rss/set_active','Controllers\Admin\PartnersController->setRssActive');
$f3->route('GET /partner_stat/@password','Controllers\StatController->indexAction');
### Admin news
$f3->route('GET /admin/news','Controllers\Admin\NewsController->indexAction');
$f3->route('GET /admin/news/@page','Controllers\Admin\NewsController->indexAction');
$f3->route('GET /admin/news/add','Controllers\Admin\NewsController->addAction');
$f3->route('POST /admin/news/add','Controllers\Admin\NewsController->addActionPost');
$f3->route('GET /admin/news/@id/delete','Controllers\Admin\NewsController->deleteAction');
$f3->route('GET /admin/news/@id/edit','Controllers\Admin\NewsController->editAction');
$f3->route('POST /admin/news/@id/edit','Controllers\Admin\NewsController->editActionPost');
$f3->route('POST /admin/news/duplicate','Controllers\Admin\NewsController->duplicateActionPost');
$f3->route('POST /admin/news/add-to-duplicated','Controllers\Admin\NewsController->addToDuplicatedPost');
$f3->route('GET /admin/news/news-collections/@articleid','Controllers\Admin\NewsController->selectCollectionAjax');
$f3->route('POST /admin/news/selected_collections','Controllers\Admin\NewsController->selected_collections');
$f3->route('GET /admin/news/@id/set_top','Controllers\Admin\NewsController->setTopAction');
### Admin categories
$f3->route('GET /admin/categories','Controllers\Admin\CategoriesController->indexAction');
$f3->route('GET /admin/categories/@page','Controllers\Admin\CategoriesController->indexAction');
$f3->route('GET /admin/categories/add','Controllers\Admin\CategoriesController->addAction');
$f3->route('POST /admin/categories/add','Controllers\Admin\CategoriesController->addActionSave');
$f3->route('GET /admin/categories/@id/delete','Controllers\Admin\CategoriesController->deleteAction');
$f3->route('GET /admin/categories/@id/edit','Controllers\Admin\CategoriesController->editAction');
$f3->route('POST /admin/categories/@id/edit','Controllers\Admin\CategoriesController->editActionPost');
### Admin telegram channels
$f3->route('GET /admin/telegram-channels','Controllers\Admin\TelegramChannelsController->indexAction');
$f3->route('GET /admin/telegram-channels/@page','Controllers\Admin\TelegramChannelsController->indexAction');
$f3->route('GET /admin/telegram-channels/add','Controllers\Admin\TelegramChannelsController->addAction');
$f3->route('GET /admin/telegram-channels/loadChennel','Controllers\Admin\TelegramChannelsController->ajaxLoadChannel');
$f3->route('POST /admin/telegram-channels/add','Controllers\Admin\TelegramChannelsController->addActionSave');
$f3->route('GET /admin/telegram-channels/@id/delete','Controllers\Admin\TelegramChannelsController->deleteAction');
$f3->route('GET /admin/telegram-channels/@id/edit','Controllers\Admin\TelegramChannelsController->editAction');
$f3->route('POST /admin/telegram-channels/@id/edit','Controllers\Admin\TelegramChannelsController->editActionPost');
$f3->route('POST /admin/telegram-channels/set_active','Controllers\Admin\TelegramChannelsController->setActive');
### Admin Telegram posts
$f3->route('GET /admin/telegram-posts','Controllers\Admin\TelegramPostsController->indexAction');
$f3->route('GET /admin/telegram-posts/@page','Controllers\Admin\TelegramPostsController->indexAction');
$f3->route('GET /admin/telegram-posts/@id/delete','Controllers\Admin\TelegramPostsController->deleteAction');
$f3->route('GET /admin/telegram-posts/@id/edit','Controllers\Admin\TelegramPostsController->editAction');
$f3->route('POST /admin/telegram-posts/@id/edit','Controllers\Admin\TelegramPostsController->editActionPost');
### Admin pages
$f3->route('GET /admin/pages','Controllers\Admin\PagesController->pagesAction');
$f3->route('GET /admin/pages/@page','Controllers\Admin\PagesController->pagesAction');
$f3->route('GET /admin/pages/add','Controllers\Admin\PagesController->pagesAddAction');
$f3->route('POST /admin/pages/add','Controllers\Admin\PagesController->pagesSaveAction');
$f3->route('GET /admin/pages/@pageid','Controllers\Admin\PagesController->pagesAction');
$f3->route('GET /admin/page/@pageid','Controllers\Admin\PagesController->pageActionShow');
$f3->route('POST /admin/page/@pageid','Controllers\Admin\PagesController->pageEditSave');
$f3->route('GET /admin/pages/delete/@pageid','Controllers\Admin\PagesController->pageDelete');
### Admin Users
$f3->route('GET /admin/users','Controllers\Admin\UsersController->usersAction');
$f3->route('GET /admin/users/@page','Controllers\Admin\UsersController->usersAction');
$f3->route('GET /admin/users/add','Controllers\Admin\UsersController->usersAddAction');
$f3->route('POST /admin/users/add','Controllers\Admin\UsersController->usersAddSaveAction');
$f3->route('GET /admin/user/@userid','Controllers\Admin\UsersController->usersEditAction');
$f3->route('POST /admin/user/@userid','Controllers\Admin\UsersController->usersSaveAction');
$f3->route('GET /admin/users/delete/@userid','Controllers\Admin\UsersController->usersDeleteAction');
### Admin Collections
$f3->route('GET /admin/collections','Controllers\Admin\CollectionsController->indexAction');
$f3->route('GET /admin/collections/@page','Controllers\Admin\CollectionsController->indexAction');
$f3->route('GET /admin/collections/add','Controllers\Admin\CollectionsController->addAction');
$f3->route('POST /admin/collections/add','Controllers\Admin\CollectionsController->addActionPost');
$f3->route('GET /admin/collections/@id/delete','Controllers\Admin\CollectionsController->deleteAction');
$f3->route('GET /admin/collections/@id/edit','Controllers\Admin\CollectionsController->editAction');
$f3->route('POST /admin/collections/@id/edit','Controllers\Admin\CollectionsController->editActionPost');
$f3->route('POST /admin/collections/set_active','Controllers\Admin\CollectionsController->setActive');
### Admin Profile
$f3->route('GET /admin/profile','Controllers\Admin\AdminController->profileAction');
$f3->route('POST /admin/profile','Controllers\Admin\AdminController->profileActionSave');
//$f3->session = new Session();
// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
$f3->run();

?>