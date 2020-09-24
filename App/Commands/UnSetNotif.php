<?php

namespace App\Commands;

class UnSetNotif extends BaseCommand {

    function processCommand($par = false)
    {
        $this->db->table('userList')->where('chatId', $this->tgParser::getChatId())->update(['notification' => 0]);
        $this->triggerCommand(MainMenu::class, 'Теперь Вы не будете получать уведомления');
    }
}