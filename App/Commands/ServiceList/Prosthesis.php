<?php

namespace App\Commands\ServiceList;

use App\Commands\BaseCommand;
use App\TgHelpers\TelegramKeyboard;

class Prosthesis extends BaseCommand {

    function processCommand($par = false)
    {
        TelegramKeyboard::$list = [
            ['title' => $this->text['mapp'], 'a' => 'mapp'],
            ['title' => $this->text['back_to_service_list'], 'a' => 'bServ'],
        ];
        TelegramKeyboard::build();
        $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['prosthesis_about'], TelegramKeyboard::get());

    }

}