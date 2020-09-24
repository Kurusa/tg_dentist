<?php

namespace App\TgHelpers;

class TelegramKeyboard {

    static $columns = 1;
    static $list;

    static $buttonText = 'title';
    static $nullText = false;

    static $action;
    static $id;
    static $aid = '';

    static $buttons = [];

    static function build()
    {
        if (self::$list) {
            $one_row = [];

            foreach (self::$list as $key => $listKey) {
                $one_row[] = [
                    'text' => self::$nullText ? $listKey : $listKey[self::$buttonText],
                    'callback_data' => json_encode([
                        'a' => self::$action ? self::$action : $listKey['a'],
                        'id' => self::$id ? $listKey[self::$id] : $key,
                        'aid' => self::$aid ? $listKey[self::$aid] : ''
                    ]),
                ];

                if (count($one_row) == self::$columns) {
                    self::$buttons[] = $one_row;
                    $one_row = [];
                }
            }

            if (count($one_row) > 0) {
                self::$buttons[] = $one_row;
            }
        }
    }

    static function addButton($text, $callback)
    {
        self::$buttons[] = [[
            'text' => $text,
            'callback_data' => json_encode($callback),
        ]];
    }

    static function addButtonUrl($text, $url)
    {
        self::$buttons[] = [[
            'text' => $text,
            'url' => $url
        ]];
    }

    static function get()
    {
        return self::$buttons;
    }

}