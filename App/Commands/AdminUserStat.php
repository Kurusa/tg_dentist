<?php

namespace App\Commands;

use App\TgHelpers\TelegramKeyboard;

class AdminUserStat extends BaseCommand {

    function processCommand($par = false)
    {
        switch ($this->tgParser::getCallbackByKey('a')) {
            case 'birthL':
                $text = 'Дни рождения: ' . "\n";
                $data = $this->db->query('SELECT * FROM userList WHERE birthday != ""');
                foreach ($data as $datum) {
                    $text .= $datum['fullName'] ? $datum['fullName'] : $datum['userName'];
                    $text .= "\n";
                    $text .= $datum['birthday'];
                    $text .= "\n \n";
                }
                $this->tg->sendMessage($text);
                exit();
            case 'notifL':
                $text = 'Подписки на уведомления: ' . "\n";
                $data = $this->db->query('SELECT * FROM userList WHERE notification = 1');
                foreach ($data as $datum) {
                    $text .= $datum['fullName'] ? $datum['fullName'] : $datum['userName'];
                    $text .= "\n";
                    $text .= $datum['phoneNumber'];
                    $text .= "\n \n";
                }
                $this->tg->sendMessage($text);
                exit();
        }
        TelegramKeyboard::addButton('кто указал день рождения', ['a' => 'birthL']);
        TelegramKeyboard::addButton('кто подписан на уведомления', ['a' => 'notifL']);
        $this->tg->sendMessageWithInlineKeyboard('Список', TelegramKeyboard::get());
    }

}