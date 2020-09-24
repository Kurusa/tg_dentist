<?php

namespace App\Commands;

use App\TgHelpers\GoogleClient;
use App\TgHelpers\TelegramKeyboard;

class AdminRecords extends BaseCommand {

    function processCommand($par = false)
    {
        $action = $this->tgParser::getCallbackByKey('a');
        switch ($action) {
            case 'my_record_admin':
                $recordId = $this->tgParser::getCallbackByKey('id');
                $data = $this->db->table('adminRecordList')->where('id', $recordId)->select()->results();
                $buttonList = [
                    ['title' => $this->text['cancel_record'], 'a' => 'cancel_record_admin', 'id' => $recordId],
                ];
                TelegramKeyboard::$id = 'id';
                TelegramKeyboard::$list = $buttonList;
                TelegramKeyboard::build();
                TelegramKeyboard::addButton($this->text['back'], ['a' => 'admin_records_back']);
                $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(),
                    $this->text['your_record'] . "\n<b>" . $this->getProcedureTitle($data[0]) . ' ' . $data[0]['exact_date'] . "</b>" . "\n" . 'Для ' . $data[0]['fullName'], TelegramKeyboard::get());
                exit();
            case 'cancel_record_admin':
                $this->tg->deleteMessage($this->tgParser::getMsgId());
                $recordId = $this->tgParser::getCallbackByKey('id');
                TelegramKeyboard::addButton('Да, отменять', ['a' => 'admin_cancel_confirm', 'id' => $recordId]);
                TelegramKeyboard::addButton($this->text['back'], ['a' => 'admin_records_back']);
                $this->tg->sendMessageWithInlineKeyboard($this->text['cancel_confirm'] . '?', TelegramKeyboard::get());
                exit();
            case 'admin_cancel_confirm':
                $this->tg->deleteMessage($this->tgParser::getMsgId());
                $recordId = $this->tgParser::getCallbackByKey('id');
                $data = $this->db->table('adminRecordList')->where('id', $recordId)->select()->results();
                $googleClient = new GoogleClient();
                $googleClient->delete($data[0]['event_id']);
                $this->db->query('DELETE FROM recordListProcedures WHERE recordId = ' . $recordId);
                $this->db->query('DELETE FROM adminRecordList WHERE id = ' . $recordId);
                $this->triggerCommand(MainMenu::class, 'Готово');
                exit();
            case 'admin_cancel_confirm0':
                $recordId = $this->tgParser::getCallbackByKey('id');
                $data = $this->db->table('recordList')->where('id', $recordId)->select()->results();
                $googleClient = new GoogleClient();
                $googleClient->delete($data[0]['event_id']);
                $this->db->query('DELETE FROM recordListProcedures WHERE recordId = ' . $recordId);
                $this->db->query('DELETE FROM recordList WHERE id = ' . $recordId);
                $this->tg->deleteMessage($this->tgParser::getMsgId());
                $this->tg->sendMessage('Запись отменена');
                exit();

        }
        $data = $this->db->query('SELECT * FROM adminRecordList WHERE done = 1 AND DATE(exact_date) > CURDATE() ORDER BY exact_date');
        $buttonList = [];
        if ($data[0]) {
            foreach ($data as $item) {
                $buttonList[] = ['title' => $item['fullName'] . ' ' . $item['exact_date'], 'id' => $item['id']];
            }
            TelegramKeyboard::$list = $buttonList;
            TelegramKeyboard::$id = 'id';
            TelegramKeyboard::$action = 'my_record_admin';
            TelegramKeyboard::build();
            if ($this->tgParser::getCallbackByKey('a') == 'admin_records_back') {
                $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), 'Нажав на кнопку, Вы сможете отменить запись', TelegramKeyboard::get());
            } else {
                $this->tg->sendMessageWithInlineKeyboard('Нажав на кнопку, Вы сможете отменить запись', TelegramKeyboard::get());
            }
        } else {
            $this->tg->sendMessage($this->text['no_records']);
        }
    }

    private
    function getProcedureTitle($data)
    {
        $procedureTitle = '';
        if ($data['procedure_id'] === '0' || $data['procedure_id']) {
            foreach ($this->text['few_services'] as $service) {
                if ($service['id'] == $data['procedure_id']) {
                    $procedureTitle = mb_strtolower($service['no_title']) . ', ';
                }
            }
        } else {
            $proceduresIdList = $this->db->table('recordListProcedures')->where('recordId', $data['id'])->select()->results();
            foreach ($this->text['few_services'] as $key => $item) {
                foreach ($proceduresIdList as $procedureId) {
                    if ($item['id'] == $procedureId['procedureId']) {
                        $procedureTitle .= mb_strtolower($this->text['few_services'][$key]['no_title']) . ', ';
                    }
                }
            }
        }
        return $procedureTitle;
    }

}