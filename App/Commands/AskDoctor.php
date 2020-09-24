<?php

namespace App\Commands;

use App\TgHelpers\TelegramKeyboard;

class AskDoctor extends BaseCommand {

    function processCommand($par = false)
    {
        $action = $this->tgParser::getCallbackByKey('a');
        TelegramKeyboard::$columns = 3;
        TelegramKeyboard::$list = [
            ['title' => 1, 'id' => 1],
            ['title' => 2, 'id' => 2],
            ['title' => 3, 'id' => 3],
            ['title' => 4, 'id' => 4],
            ['title' => 5, 'id' => 5],
            ['title' => 6, 'id' => 6],
        ];
        TelegramKeyboard::$buttonText = 'title';
        TelegramKeyboard::$action = 'ask_doctor';
        TelegramKeyboard::$id = 'id';
        TelegramKeyboard::build();

        if ($action == 'ask_doctor') {
            TelegramKeyboard::addButton('Назад к вопросам ⬅', ['a' => 'back_ask_doctor']);
            $text = $this->text['to_doctor_' . $this->tgParser::getCallbackByKey('id')];
            $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $text, TelegramKeyboard::get());
        } elseif ($action == 'back_ask_doctor') {
            $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['to_doctor_about'], TelegramKeyboard::get());
        } else {
            $this->tg->sendMessageWithInlineKeyboard($this->text['to_doctor_about'], TelegramKeyboard::get());
        }
    }

}