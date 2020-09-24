<?php

namespace App\Commands;

use App\TgHelpers\TelegramKeyboard;

class FAQ extends BaseCommand {

    function processCommand($par = false)
    {
        TelegramKeyboard::$list = [
            ['title' => $this->text['to_doctor'], 'a' => 'to_doctor'],
            ['title' => $this->text['another_questions'], 'a' => 'another_questions'],
        ];
        TelegramKeyboard::build();

        $this->tg->sendMessageWithInlineKeyboard($this->text['faq_about'], TelegramKeyboard::get());
    }

}