<?php

namespace App\TgHelpers;

class TelegramParser {

    private static $data;

    public function __construct($data)
    {
        self::$data = $data;
    }

    public static function getCallbackId()
    {
        return self::$data['id'];
    }

    public static function getMsgId()
    {
        return self::$data['message']['message_id'];
    }

    public static function getUserName()
    {
        return strval(self::$data['message']['chat']['username']);
    }

    public static function getMessage()
    {
        return strval(self::$data['message']['text']);
    }

    public static function getChatId()
    {
        return intval(self::$data['message']['chat']['id']);
    }

    public static function getWholeCallback()
    {
        return self::$data['data'];
    }

    public static function getCallbackByKey($key)
    {
        return json_decode(self::getWholeCallback(), true)[strval($key)];
    }

}