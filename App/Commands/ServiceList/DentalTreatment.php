<?php

namespace App\Commands\ServiceList;

use App\Commands\BaseCommand;
use App\TgHelpers\TelegramKeyboard;

class DentalTreatment extends BaseCommand {

    function processCommand($par = false)
    {
        switch ($this->tgParser::getCallbackByKey('a')) {
            case 'service_a':
                TelegramKeyboard::$list = [
                    ['title' => $this->text['cost'], 'a' => 'c0'],
                    ['title' => $this->text['work_templates'], 'a' => 'templ_0'],
                    ['title' => $this->text['back_to_service_list'], 'a' => 'bServ'],
                ];
                TelegramKeyboard::build();
                $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['service_list_0'], TelegramKeyboard::get());
                break;
            case 'c0':
                $msgId = $this->tgParser::getCallbackByKey('mid');
                if ($msgId) {
                    $list = explode('.', $msgId);
                    $this->tg->deleteMessage($list[0]);
                    $this->tg->deleteMessage($list[1]);
                }
                TelegramKeyboard::$list = [
                    ['title' => $this->text['work_templates'], 'a' => 'templ_0'],
                    ['title' => $this->text['back_to_service_list'], 'a' => 'bServ'],
                ];
                TelegramKeyboard::build();
                $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['service_list_0_cost'], TelegramKeyboard::get());
                break;
            case 'templ_0':
                $result = $this->tg->sendMediaGroup([
                    getenv('HTTP_HOST') . '/src/service_list_0_temp0.png',
                    getenv('HTTP_HOST') . '/src/service_list_0_temp1.png',
                ]);
                TelegramKeyboard::addButton($this->text['cost'], ['a' => 'c0', 'mid' => $result['result'][0]['message_id'].'.'.$result['result'][1]['message_id']]);
                TelegramKeyboard::addButton($this->text['back_to_service_list'], ['a' => 'bServ', 'mid' => $result['result'][0]['message_id'].'.'.$result['result'][1]['message_id']]);
                $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['work_templates'], TelegramKeyboard::get());
                break;
        }
    }

}