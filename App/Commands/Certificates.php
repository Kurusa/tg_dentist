<?php

namespace App\Commands;

use App\TgHelpers\TelegramKeyboard;

class Certificates extends BaseCommand {

    function processCommand($par = false)
    {
        if ($this->tgParser::getCallbackByKey('a') == 'next_cert') {
            TelegramKeyboard::$list = [
                ['title' => $this->text['mapp'], 'a' => 'mapp'],
                ['title' => $this->text['chat'], 'a' => 'chat'],
                ['title' => $this->text['service_list'], 'a' => 'service_list'],
            ];
            TelegramKeyboard::build();
            $this->tg->sendMessageWithInlineKeyboard($this->text['next_action'], TelegramKeyboard::get());
            exit();
        }
        $this->tg->sendMessage($this->text['more_certificates']);
        $this->tg->sendMediaGroup([
            getenv('HTTP_HOST') . '/src/cert0.png',
            getenv('HTTP_HOST') . '/src/cert1.png',
            getenv('HTTP_HOST') . '/src/cert2.png',
            getenv('HTTP_HOST') . '/src/cert3.png',
            getenv('HTTP_HOST') . '/src/cert4.png',
            getenv('HTTP_HOST') . '/src/cert5.png',
        ]);
        TelegramKeyboard::$list = [
            ['title' => $this->text['next'], 'a' => 'next_cert'],
        ];
        TelegramKeyboard::build();
        $this->tg->sendMessageWithInlineKeyboard($this->text['good_dentist'], TelegramKeyboard::get());
    }

}