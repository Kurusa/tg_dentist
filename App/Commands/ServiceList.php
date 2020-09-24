<?php

namespace App\Commands;

use App\Commands\ServiceList\AestheticDentistry;
use App\Commands\ServiceList\DentalTreatment;
use App\Commands\ServiceList\Hygiene;
use App\Commands\ServiceList\Prosthesis;
use App\Commands\ServiceList\TeethAlignment;
use App\TgHelpers\TelegramKeyboard;

class ServiceList extends BaseCommand {

    function processCommand($par = false)
    {
        $action = $this->tgParser::getCallbackByKey('a');
        if ($action == 'service_a') {
            $id = $this->tgParser::getCallbackByKey('id');
            switch ($id) {
                //Лечение зубов
                case 0:
                    $this->triggerCommand(DentalTreatment::class);
                    break;
                //Выравнивание
                case 1:
                    $this->triggerCommand(TeethAlignment::class);
                    break;
                //Эстетическая стоматология
                case 2:
                    $this->triggerCommand(AestheticDentistry::class);
                    break;
                //Гигиена
                case 3:
                    $this->triggerCommand(Hygiene::class);
                    break;
                //Протезирование
                case 4:
                    $this->triggerCommand(Prosthesis::class);
                    break;
            }
            exit;
        } else {
            TelegramKeyboard::$list = $this->text['service_list_actions'];
            TelegramKeyboard::$action = 'service_a';
            TelegramKeyboard::build();
            if ($action == 'bServ') {
                $msgId = $this->tgParser::getCallbackByKey('mid');
                if ($msgId) {
                    $list = explode('.', $msgId);
                    foreach ($list as $value) {
                        if ($value) {
                            $this->tg->deleteMessage($value);
                        }
                    }
                }
                $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['see_more'], TelegramKeyboard::get());
            } else {
                $this->tg->sendMessageWithInlineKeyboard($this->text['see_more'], TelegramKeyboard::get());
            }
        }
    }

}