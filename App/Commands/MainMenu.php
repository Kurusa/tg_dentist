<?php

namespace App\Commands;

class MainMenu extends BaseCommand {

    function processCommand($par = false)
    {
        $this->db->table('userList')->where('chatId', $this->chatId)->update(['mode' => 'done']);
        if ($this->userData['recordId'] > 0) {
            if ($this->tgParser::getMessage() == 'Отмена') {
                $this->db->query('DELETE FROM recordListProcedures WHERE recordId = ' . $this->userData['recordId']);
                $this->db->query('DELETE FROM adminRecordList WHERE id = ' . $this->userData['recordId']);
            }
            $this->db->table('userList')->where('chatId', $this->chatId)->update(['recordId' => 0]);
        } else {
            $data = $this->db->table('recordList')->where('chatId', $this->chatId)->where('done', 0)->select()->results();
            if ($data[0]) {
                $this->db->query('DELETE FROM recordListProcedures WHERE recordId = ' . $data[0]['id']);
                $this->db->query('DELETE FROM recordList WHERE chatId = ' . $this->chatId . ' AND done = 0');
            }
        }
        if ($this->userData['isAdmin'] == '1') {
            $this->tg->sendMessageWithKeyboard($par ? $par : $this->text['main_menu'], [
                ['создать запись', 'отменить запись'],
                ['статистика рассылок']
            ]);
        } else {
            $this->tg->sendMessageWithKeyboard($par ? $par : $this->text['main_menu'], [
                [$this->text['mapp']],
                [$this->text['service_list'], $this->text['my_records']],
                [$this->text['about_doctor'], $this->text['faq']],
                [$this->text['how_to_drive'], $this->text['contact_us']],
                [$this->text['profile']]
            ]);
        }
    }

}