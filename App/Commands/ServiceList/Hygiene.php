<?php

namespace App\Commands\ServiceList;

use App\Commands\BaseCommand;
use App\Commands\MainMenu;
use App\TgHelpers\TelegramKeyboard;

class Hygiene extends BaseCommand {

    function processCommand($par = false)
    {
        switch ($this->tgParser::getCallbackByKey('a')) {
            case 'service_a':
                //Send action list
                TelegramKeyboard::build();
                TelegramKeyboard::addButton($this->text['mapp'], ['a' => 'mapp']);
                TelegramKeyboard::addButton($this->text['get_notification'], ['a' => 'sl_3_action', 'id' => 0]);
                TelegramKeyboard::addButton($this->text['back_to_service_list'], ['a' => 'bServ']);
                $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['service_list_3'], TelegramKeyboard::get());
                break;
            case 'sl_3_action':
                if ($this->tgParser::getCallbackByKey('id') == 0) {
                    $this->db->table('userList')->where('chatId', $this->chatId)->update(['notification' => 1]);
                    $this->tg->deleteMessage($this->tgParser::getMsgId());
                    $this->triggerCommand(MainMenu::class, $this->text['ready_notification']);
                }
                break;
        }
    }

}