<?php

namespace App\Commands\ServiceList;

use App\Commands\BaseCommand;
use App\TgHelpers\TelegramKeyboard;

class TeethAlignment extends BaseCommand {

    function processCommand($par = false)
    {
        switch ($this->tgParser::getCallbackByKey('a')) {
            case 'service_a':
                //Send action list
                TelegramKeyboard::$list = $this->text['service_list_1_actions'];
                TelegramKeyboard::build();
                TelegramKeyboard::addButton($this->text['back_to_service_list'], ['a' => 'bServ']);
                $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['service_list_1'], TelegramKeyboard::get());
                break;
            case 'sl_1_action':
                //First action list
                switch ($this->tgParser::getCallbackByKey('id')) {
                    //Брекеты
                    case 0:
                        TelegramKeyboard::build();
                        TelegramKeyboard::addButton($this->text['which_photos'], ['a' => 'bracA', 'id' => 0]);
                        TelegramKeyboard::addButton($this->text['cost_notion'], ['a' => 'bracA', 'id' => 1]);
                        TelegramKeyboard::addButton($this->text['work_templates'], ['a' => 'bracA', 'id' => 2]);
                        TelegramKeyboard::addButton($this->text['back_to_service_list'], ['a' => 'bServ']);
                        $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['braces'], TelegramKeyboard::get());
                        break;
                    //Элайнеры
                    case 1:
                        TelegramKeyboard::build();
                        TelegramKeyboard::addButton($this->text['eliners_cost'], ['a' => 'elinA', 'id' => 0]);
                        TelegramKeyboard::addButton($this->text['work_templates'], ['a' => 'elinA', 'id' => 1]);
                        TelegramKeyboard::addButton($this->text['back_to_service_list'], ['a' => 'bServ']);
                        $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['eliners_about'], TelegramKeyboard::get());
                        break;
                    //Пластины
                    case 2:
                        TelegramKeyboard::build();
                        TelegramKeyboard::addButton($this->text['which_photos'], ['a' => 'platA', 'id' => 0]);
                        TelegramKeyboard::addButton($this->text['work_templates'], ['a' => 'platA', 'id' => 1]);
                        TelegramKeyboard::addButton($this->text['back_to_service_list'], ['a' => 'bServ']);
                        $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['plates_about'], TelegramKeyboard::get());
                        break;
                        break;
                }
                break;
            case 'platA':
                switch ($this->tgParser::getCallbackByKey('id')) {
                    //Какие снимки нужны?🎞
                    case 0:
                        $msgId = $this->tgParser::getCallbackByKey('mid');
                        if ($msgId) {
                            $list = explode('.', $msgId);
                            foreach ($list as $key => $value) {
                                if ($value) {
                                    $this->tg->deleteMessage($value);
                                }
                            }
                        }
                        TelegramKeyboard::build();
                        TelegramKeyboard::addButtonUrl($this->text['planmeca_address'], 'http://www.3dcenter.com.ua/adresa');
                        TelegramKeyboard::addButton($this->text['work_templates'], ['a' => 'platA', 'id' => 1]);
                        TelegramKeyboard::addButton($this->text['back_to_service_list'], ['a' => 'bServ']);
                        $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['plates_photos'], TelegramKeyboard::get());
                        break;
                    //Примеры работ📸
                    case 1:
                        $result = $this->tg->sendMediaGroup([
                            getenv('HTTP_HOST') . '/src/plates_temp0.png',
                        ]);
                        TelegramKeyboard::addButton($this->text['which_photos'], ['a' => 'platA', 'id' => 0, 'mid' => $result['result'][0]['message_id']]);
                        TelegramKeyboard::addButton($this->text['back_to_service_list'], ['a' => 'bServ', 'mid' => $result['result'][0]['message_id']]);
                        $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['plates_temp'], TelegramKeyboard::get());
                        break;
                }
                break;
            case 'elinA':
                switch ($this->tgParser::getCallbackByKey('id')) {
                    //Стоимость элайнеров💰
                    case 0:
                        $msgId = $this->tgParser::getCallbackByKey('mid');
                        if ($msgId) {
                            $list = explode('.', $msgId);
                            foreach ($list as $key => $value) {
                                if ($value) {
                                    $this->tg->deleteMessage($value);
                                }
                            }
                        }
                        TelegramKeyboard::addButton($this->text['work_templates'], ['a' => 'elinA', 'id' => 1]);
                        TelegramKeyboard::addButton($this->text['back_to_service_list'], ['a' => 'bServ']);
                        $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['eliners_cost_about'], TelegramKeyboard::get());
                        break;
                    //Примеры работ📸
                    case 1:
                        $result = $this->tg->sendMediaGroup([
                            getenv('HTTP_HOST') . '/src/eliners_temp0.png',
                            getenv('HTTP_HOST') . '/src/eliners_temp1.png',
                        ]);
                        TelegramKeyboard::addButton($this->text['eliners_cost'], ['a' => 'elinA', 'id' => 0, 'mid' => $result['result'][0]['message_id'] . '.' . $result['result'][1]['message_id']]);
                        TelegramKeyboard::addButton($this->text['back_to_service_list'], ['a' => 'bServ', 'mid' => $result['result'][0]['message_id'] . '.' . $result['result'][1]['message_id']]);
                        $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['work_templates'], TelegramKeyboard::get());
                        break;
                }
                break;
            case 'bracA':
                switch ($this->tgParser::getCallbackByKey('id')) {
                    //Какие снимки нужны
                    //О консультации ❗
                    case 0:
                        $msgId = $this->tgParser::getCallbackByKey('mid');
                        if ($msgId) {
                            $list = explode('.', $msgId);
                            foreach ($list as $key => $value) {
                                if ($value) {
                                    $this->tg->deleteMessage($value);
                                }
                            }
                        }
                        TelegramKeyboard::build();
                        TelegramKeyboard::addButtonUrl($this->text['planmeca_address'], 'http://www.3dcenter.com.ua/adresa');
                        TelegramKeyboard::addButton($this->text['braces_cost'], ['a' => 'bracA', 'id' => 3]);
                        TelegramKeyboard::addButton($this->text['work_templates'], ['a' => 'bracA', 'id' => 2]);
                        TelegramKeyboard::addButton($this->text['back_to_service_list'], ['a' => 'bServ']);
                        $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['bracAs_0'], TelegramKeyboard::get());
                        break;
                    //Принцип оплаты
                    case 1:
                        $msgId = $this->tgParser::getCallbackByKey('mid');
                        if ($msgId) {
                            $list = explode('.', $msgId);
                            foreach ($list as $key => $value) {
                                if ($value) {
                                    $this->tg->deleteMessage($value);
                                }
                            }
                        }
                        TelegramKeyboard::build();
                        TelegramKeyboard::addButton($this->text['about_cost'], ['a' => 'bracA', 'id' => 0]);
                        TelegramKeyboard::addButton($this->text['work_templates'], ['a' => 'bracA', 'id' => 2]);
                        TelegramKeyboard::addButton($this->text['back_to_service_list'], ['a' => 'bServ']);
                        $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['cost_about'], TelegramKeyboard::get());
                        break;
                    //Примеры работ
                    case 2:
                        $result = $this->tg->sendMediaGroup([
                            getenv('HTTP_HOST') . '/src/braces_temp0.png',
                            getenv('HTTP_HOST') . '/src/braces_temp1.png',
                            getenv('HTTP_HOST') . '/src/braces_temp2.png',
                        ]);
                        TelegramKeyboard::build();
                        TelegramKeyboard::addButton($this->text['about_cost'], ['a' => 'bracA', 'id' => 0, 'mid' => $result['result'][0]['message_id'] . '.' . $result['result'][1]['message_id'] . '.' . $result['result'][2]['message_id']]);
                        TelegramKeyboard::addButton('Принцип оплаты💰', ['a' => 'bracA', 'id' => 1, 'mid' => $result['result'][0]['message_id'] . '.' . $result['result'][1]['message_id'] . '.' . $result['result'][2]['message_id']]);
                        TelegramKeyboard::addButton($this->text['back_to_service_list'], ['a' => 'bServ', 'mid' => $result['result'][0]['message_id'] . '.' . $result['result'][1]['message_id'] . '.' . $result['result'][2]['message_id']]);
                        $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['work_templates'], TelegramKeyboard::get());
                        break;
                    //Цена консультации
                    case 3:
                        TelegramKeyboard::build();
                        TelegramKeyboard::addButtonUrl($this->text['planmeca_address'], 'http://www.3dcenter.com.ua/adresa');
                        TelegramKeyboard::addButton('Принцип оплаты💰', ['a' => 'bracA', 'id' => 1]);
                        TelegramKeyboard::addButton($this->text['work_templates'], ['a' => 'bracAs', 'id' => 2]);
                        TelegramKeyboard::addButton($this->text['back_to_service_list'], ['a' => 'bServ']);
                        $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['braces_cost_about'], TelegramKeyboard::get());
                        break;
                }
                break;
        }
    }

}