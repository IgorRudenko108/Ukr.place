<?php

namespace Helpers;

class RssHelper
{
    public static function parseTagsAndAttr($item, $tag)
    {
        try {
            if (str_contains($tag, ".attr.")) {
                $datas = explode('.', $tag);
                $i = 0;
                foreach ($datas as $d) {
                    if ($d == "tag") {
                        $tagName = $datas[$i + 1];
                    }
                    if ($d == "attr") {
                        $attrName = $datas[$i + 1];
                    }
                    $i++;
                }
                if(isset($item->$tagName->attributes()->$attrName))
                {
                    $text = $item->$tagName->attributes()->$attrName;
                    return $text;
                } else {
                    return "";
                }
            }

            if (str_starts_with($tag, "tag.")) {
                $tagName = explode('.', $tag);
                $tagName = $tagName[1];
                $title = trim($item->$tagName);
                return $title;
            }
        } catch (\Exception $ex)
        {
            return "";
        }
    }
}