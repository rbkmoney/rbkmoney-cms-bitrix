<?php


/**
 * Class RBKmoneyUrlHelper
 */
class RBKmoneyUrlHelper
{

    public static function getCurrentSchema()
    {
        return ((isset($_SERVER['HTTPS']) && preg_match("/^on$/i", $_SERVER['HTTPS'])) ? "https" : "http");
    }

    public static function getCurrentHostName()
    {
        return $_SERVER['HTTP_HOST'];
    }

    public static function getBaseUrlWithOutSlash()
    {
        return rtrim(static::getCurrentSchema() . '://' . static::getCurrentHostName(), '/');
    }

    public static function getBaseUrlWithSlash()
    {
        return rtrim(static::getCurrentSchema() . '://' . static::getCurrentHostName(), '/') . '/';
    }

}
