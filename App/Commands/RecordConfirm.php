<?php

namespace App\Commands;

class RecordConfirm extends BaseCommand {

    function processCommand($par = false)
    {
        if ($this->tgParser::getCallbackByKey('a') == 'will_be') {
            $this->tg->sendMessageWithKeyboard('Спасибо за подтверждение! Ждем Вас!', [
                [$this->text['contact_us']],
                [$this->text['how_to_drive']],
                [$this->text['main_menu']],
            ]);
        }
    }

}