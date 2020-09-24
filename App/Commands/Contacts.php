<?php

namespace App\Commands;

class Contacts extends BaseCommand {

    function processCommand($par = false)
    {
        $msgId = $this->tgParser::getCallbackByKey('mid');
        if ($msgId) {
            $list = explode('.', $msgId);
            foreach ($list as $value) {
                if ($value) {
                    $this->tg->deleteMessage($value);
                }
            }
        }
        $this->triggerCommand(MainMenu::class, $this->text['about_contact_us']);
    }
}