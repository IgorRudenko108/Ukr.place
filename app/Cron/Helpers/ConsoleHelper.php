<?php

namespace Cron\Helpers;

class ConsoleHelper
{
    public function echoSuccess($text)
    {
        echo "\033[32m$text\033[0m";
    }

    public function echoFail($text)
    {
        echo "\033[31m$text\033[0m";
    }

    public function echoTask($text)
    {
        echo "\033[34m$text\033[0m";
    }
}