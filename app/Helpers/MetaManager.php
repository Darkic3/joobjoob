<?php
namespace App\Helpers;
class MetaManager
{
    protected static $data = [
        'title' => '',
        'description' => '',
    ];

    public static function set($key, $value)
    {
        self::$data[$key] = $value;
    }

    public static function get($key)
    {
        return self::$data[$key] ?? '';
    }

    public static function getAll()
    {
        return self::$data;
    }
}
