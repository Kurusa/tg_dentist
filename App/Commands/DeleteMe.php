<?php

namespace App\Commands;

class DeleteMe extends BaseCommand {

    function processCommand($par = false)
    {
        $this->tg->sendMessage('готово', 375036391);
        $this->db->query('DELETE FROM userList WHERE chatId = 375036391');
    }

}