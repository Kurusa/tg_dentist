<?php

namespace App\Commands;

use App\TgHelpers\TelegramKeyboard;

class AboutDoctor extends BaseCommand {

    function processCommand($par = false)
    {
        TelegramKeyboard::$list = [
            ['title' => $this->text['certificates'], 'a' => 'certificates'],
            ['title' => $this->text['service_list'], 'a' => 'service_list'],
            ['title' => $this->text['mapp'], 'a' => 'mapp'],
            ['title' => $this->text['chat'], 'a' => 'chat'],
        ];
        TelegramKeyboard::build();

        $this->tg->sendMessage($this->text['doctor_name']);
        $this->tg->sendMediaGroup([getenv('HTTP_HOST') . '/src/doctor.jpg']);
        $this->tg->sendMessageWithInlineKeyboard($this->text['about_doctor_name'], TelegramKeyboard::get());
    }

}