<?php

namespace App\Commands;

class AskBirthday extends BaseCommand {

    private $mode = 'birthday';

    function processCommand($par = false)
    {
        if ($this->userData['mode'] == $this->mode) {
            $msg = $this->tgParser::getMessage();
            if (preg_match("^\\d{1,2}.\\d{2}.\\d{4}^", $msg)) {
                $this->db->table('userList')->where('chatId', $this->chatId)->update(['birthday' => $msg]);
                $this->tg->sendMessage($this->text['thank_for_birthday']);
                $this->tg->sendMessage($this->text['about_discount']);
                $this->triggerCommand(MainMenu::class);
            } elseif ($msg == $this->text['enter_birthday']) {
                $this->tg->sendMessageWithKeyboard($this->text['birthday_rule'], [[$this->text['skip']]]);
            }
        } else {
            $this->db->table('userList')->where('chatId', $this->chatId)->update(['mode' => $this->mode]);
            $this->tg->sendMessage('Новый пользователь: ' . $this->userData['userName'], 480606850);
            $this->tg->sendMessageWithKeyboard($this->text['ask_birthday'], [[$this->text['enter_birthday']], [$this->text['skip']]]);
        }
    }
}