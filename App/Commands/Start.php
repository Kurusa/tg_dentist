<?php

namespace App\Commands;

class Start extends BaseCommand {

    private $mode = 'start';

    function processCommand($par = false)
    {
        if (empty($this->userData)) {
            $this->db->table('userList')->insert(['chatId' => $this->tgParser::getChatId(), 'userName' => $this->tgParser::getUserName(), 'mode' => $this->mode]);
            $this->tg->sendMessageWithKeyboard($this->text['hello'], [[ $this->text['click_here'] ]]);
        } else {
            $this->triggerCommand(MainMenu::class);
        }
    }
}

