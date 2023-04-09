<?php namespace Layerok\TgMall\Classes\Traits;

use Lang as Facade;

trait Lang
{
    public static function lang($key, $params = [])
    {
        $pluginPrefix = "layerok.tgmall::lang.telegram.";
        return Facade::get($pluginPrefix . $key, $params);
    }


}
