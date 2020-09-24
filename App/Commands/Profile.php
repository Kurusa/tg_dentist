<?php

namespace App\Commands;

class Profile extends BaseCommand {

    function processCommand($par = false)
    {
        if ($this->tgParser::getMessage() == $this->text['change_fio']) {
            $this->db->table('userList')->where('chatId', $this->chatId)->update(['mode' => 'full_name_profile']);
            $this->tg->sendMessageWithKeyboard($this->text['write_full_name'], [[$this->text['cancel']]]);
            exit;
        }
        if ($this->tgParser::getMessage() == $this->text['change_phone_number']) {
            $this->db->table('userList')->where('chatId', $this->chatId)->update(['mode' => 'phone_number_profile']);
            $this->tg->sendMessageWithKeyboard($this->text['write_phone_number'], [[$this->text['cancel']]]);
            exit;
        }

        if ($this->userData['mode'] == 'full_name_profile') {
            $fio = $this->tgParser::getMessage();
            if (strlen($fio) > 5) {
                $this->tg->deleteMessage($this->tgParser::getMsgId());
                $this->db->table('userList')->where('chatId', $this->chatId)->update(['fullName' => $fio]);
                $this->triggerCommand(MainMenu::class, $this->text['change_fio_success']);
            } else {
                $this->tg->sendMessage($this->text['wrong_full_name']);
            }
            exit;
        }
        if ($this->userData['mode'] == 'phone_number_profile') {
            $phoneNumber = $this->tgParser::getMessage();
            if (strlen($phoneNumber) > 5) {
                $this->db->table('userList')->where('chatId', $this->chatId)->update(['phoneNumber' => $phoneNumber]);
                $this->tg->deleteMessage($this->tgParser::getMsgId());
                $this->triggerCommand(MainMenu::class, $this->text['change_phone_number_success']);
            } else {
                $this->tg->sendMessage($this->text['wrong_phone_number']);
            }
            exit;
        }

        $text = '';
        if ($this->userData['fullName']) {
            $text .= "\n".'<b>Ваше ФИО</b>: '.$this->userData['fullName'];
        } else {
            $text .= "\n".'Вы пока что не указали свое ФИО.';
        }
        if ($this->userData['phoneNumber']) {
            $text .= "\n".'<b>Ваш номер телефона</b>: '.$this->userData['phoneNumber'];
        } else {
            $text .= "\n".'Вы пока что не указали свой номер телефона.';
        }
        $this->tg->sendMessageWithKeyboard($this->text['here_you_can_change'].$text, [
            [$this->text['change_fio'], $this->text['change_phone_number']],
            [$this->text['cancel']]
        ]);
    }

}