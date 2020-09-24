<?php

namespace App\Commands\ServiceList;

use App\Commands\BaseCommand;
use App\TgHelpers\TelegramKeyboard;

class AestheticDentistry extends BaseCommand {

    function processCommand($par = false)
    {
        switch ($this->tgParser::getCallbackByKey('a')) {
            case 'service_a':
                //Send action list
                TelegramKeyboard::$list = $this->text['service_list_2_actions'];
                TelegramKeyboard::build();
                TelegramKeyboard::addButton($this->text['back_to_service_list'], ['a' => 'bServ']);
                $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['service_list_2'], TelegramKeyboard::get());
                break;
            case 'sl_2_action':
                //First action list
                switch ($this->tgParser::getCallbackByKey('id')) {
                    //Отбеливание
                    case 0:
                        TelegramKeyboard::build();
                        TelegramKeyboard::addButton($this->text['mapp'], ['a' => 'mapp']);
                        TelegramKeyboard::addButton($this->text['work_templates'], ['a' => 'aesthetic_action', 'id' => 0]);
                        TelegramKeyboard::addButton($this->text['back_to_service_list'], ['a' => 'bServ']);
                        $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['whitening_about'], TelegramKeyboard::get());
                        break;
                    //Виниры
                    case 1:
                        TelegramKeyboard::build();
                        TelegramKeyboard::addButton($this->text['back_to_service_list'], ['a' => 'bServ']);
                        $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['venees_about'], TelegramKeyboard::get());
                        break;
                    //Реставрация
                    case 2:
                        TelegramKeyboard::build();
                        TelegramKeyboard::addButton($this->text['send_photo'], ['a' => 'chat']);
                        TelegramKeyboard::addButton($this->text['work_templates'], ['a' => 'restoration_action', 'id' => 0]);
                        TelegramKeyboard::addButton($this->text['back_to_service_list'], ['a' => 'bServ']);
                        $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['restoration_about'], TelegramKeyboard::get());
                        break;
                        break;
                }
                break;
            case 'aesthetic_action':
                if ($this->tgParser::getCallbackByKey('id') == 0) {
                    //Примеры работ📸
                    $result = $this->tg->sendMediaGroup([
                        getenv('HTTP_HOST') . '/src/aesthetic_0_temp0.png',
                        getenv('HTTP_HOST') . '/src/aesthetic_0_temp1.png',
                    ]);
                    TelegramKeyboard::addButton($this->text['mapp'], ['a' => 'mapp', 'mid' => $result['result'][0]['message_id'].'.'.$result['result'][1]['message_id']]);
                    TelegramKeyboard::addButton($this->text['back_to_service_list'], ['a' => 'bServ', 'mid' => $result['result'][0]['message_id'].'.'.$result['result'][1]['message_id']]);
                    $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['work_templates'], TelegramKeyboard::get());
                }
                break;
            case 'restoration_action':
                if ($this->tgParser::getCallbackByKey('id') == 0) {
                    //Примеры работ📸
                    $result = $this->tg->sendMediaGroup([
                        getenv('HTTP_HOST') . '/src/restoration_temp_0.png',
                        getenv('HTTP_HOST') . '/src/restoration_temp_1.png',
                        getenv('HTTP_HOST') . '/src/restoration_temp_2.png',
                        getenv('HTTP_HOST') . '/src/restoration_temp_3.png',
                    ]);
                    TelegramKeyboard::addButton($this->text['send_photo'], ['a' => 'chat', 'mid' => $result['result'][0]['message_id'].'.'.$result['result'][1]['message_id'].'.'.$result['result'][2]['message_id'].'.'.$result['result'][3]['message_id']]);
                    TelegramKeyboard::addButton($this->text['back_to_service_list'], ['a' => 'bServ', 'mid' => $result['result'][0]['message_id'].'.'.$result['result'][1]['message_id'].'.'.$result['result'][2]['message_id'].'.'.$result['result'][3]['message_id']]);
                    $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['work_templates'], TelegramKeyboard::get());
                }
                break;
        }
    }

}