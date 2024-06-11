<?php

namespace Helpers;

use simplehtmldom\HtmlWeb;
class TelegramHelper
{
    public static function getChannelInfo($channelLink)
    {
        try{
            $doc = new HtmlWeb();
            $html = $doc->load($channelLink);
            $title = $html->find('div.tgme_page_title')[0];
            $title = $title->plaintext;
            $subscribers = $html->find('div.tgme_page_extra')[0];
            $subscribers = preg_replace("/[^,.0-9]/", '', $subscribers->plaintext);
            $subscribers = number_format($subscribers, 0, '', ' ');
            $description = $html->find('div.tgme_page_description')[0];
            $description = $description->plaintext;
            $image = $html->find('img.tgme_page_photo_image')[0];
            $image = $image->src;
            $return = [
                "title" => $title,
                "description" => $description,
                "subscribers" => $subscribers,
                "image" => $image
            ];
            return $return;
        }
        catch (\Exception $ex)
        {
            return [
                "error" => true
            ];
        }
    }
}