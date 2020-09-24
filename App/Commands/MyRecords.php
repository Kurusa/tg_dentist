<?php

namespace App\Commands;

use App\TgHelpers\GoogleClient;
use App\TgHelpers\TelegramKeyboard;

class MyRecords extends BaseCommand {

    private $recordId;

    function processCommand($par = false)
    {
        $action = $this->tgParser::getCallbackByKey('a');
        switch ($action) {
            case 'my_record':
                $recordId = $this->tgParser::getCallbackByKey('id');
                $data = $this->db->table('recordList')->where('id', $recordId)->select()->results();
                $buttonList = [
                    ['title' => $this->text['change_record_time'], 'a' => 'change_record_time', 'id' => $recordId],
                    ['title' => $this->text['cancel_record'], 'a' => 'cancel_record', 'id' => $recordId],
                ];
                TelegramKeyboard::$columns = 2;
                TelegramKeyboard::$id = 'id';
                TelegramKeyboard::$list = $buttonList;
                TelegramKeyboard::build();
                TelegramKeyboard::addButton($this->text['back'], ['a' => 'records_back']);
                $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(),
                    $this->text['your_record'] . "\n<b>" . $this->getProcedureTitle($data[0]) . ' ' . $data[0]['exact_date'] . "</b>" . "\n" . $this->text['your_record_info'], TelegramKeyboard::get());
                exit();
            case 'cancel_record':
                $recordId = $this->tgParser::getCallbackByKey('id');
                TelegramKeyboard::addButton('Да, отменять', ['a' => 'cancel_confirm', 'id' => $recordId]);
                TelegramKeyboard::addButton($this->text['change_record_time'], ['a' => 'change_record_time', 'id' => $recordId]);
                TelegramKeyboard::addButton($this->text['back'], ['a' => 'records_back']);
                $data = $this->db->table('recordList')->where('id', $recordId)->select()->results();
                $this->tg->sendMessageWithInlineKeyboard($this->text['cancel_confirm'] . $data[0]['exact_date'] . '?', TelegramKeyboard::get());
                exit();
            case 'change_record_time':
                $this->recordId = $this->tgParser::getCallbackByKey('id');
                $this->db->table('recordList')->where('id', $this->recordId)->update(['edit' => 1, 'done' => 0]);
                $this->selectHowLong();
                exit();
            case 'cancel_confirm':
                $recordId = $this->tgParser::getCallbackByKey('id');
                $data = $this->db->table('recordList')->where('id', $recordId)->select()->results();
                $googleClient = new GoogleClient();
                $googleClient->delete($data[0]['event_id']);
                $this->db->query('DELETE FROM recordListProcedures WHERE recordId = ' . $recordId);
                $this->db->query('DELETE FROM recordList WHERE id = ' . $recordId);
                $this->db->table('recordCancelsList')->insert(['chatId' => $this->chatId, 'date' => date('c', time())]);
                $this->tg->sendMessageWithKeyboard($this->text['after_cancel'], [
                    [$this->text['mapp']], [$this->text['contact_us']], [$this->text['main_menu']]
                ]);
                $count = $this->db->query('SELECT COUNT(*) AS count FROM recordCancelsList WHERE DATE(date) = CURDATE() AND chatId = ' . $this->chatId);
                if ($count[0]['count'] == 3) {
                    $this->db->table('userList')->where('chatId', $this->chatId)->update(['isBlockedByBot' => 1]);
                    $this->tg->sendMessageWithKeyboard($this->text['too_many_cancels'], [
                        [$this->text['contact_us']]
                    ]);
                }
                $this->tg->deleteMessage($this->tgParser::getMsgId());
                $adminText = 'Отмена записи ❌' . "\n";
                $adminText .= 'ФИО: ' . $this->userData['fullName']
                    . "\n" . 'Телефон: ' . $this->userData['phoneNumber'];
                $this->tg->sendMessage($adminText, 205187375);
                exit();
            case 'how_long_rec':
                $this->db->table('recordList')->where('chatId', $this->chatId)->where('edit', 1)->update(['how_long' => $this->tgParser::getCallbackByKey('id')]);
                $this->selectDay();
                exit();
        }
        switch ($this->userData['mode']) {
            case 'how_long_rec':
                $this->selectHowLong(true);
                exit();
            case 'select_day_rec':
                $this->selectDay(true);
                exit();
            case 'select_time_rec':
                $this->selectTime(true);
                exit();
        }
        $data = $this->db->table('recordList')->where('chatId', $this->chatId)->where('done', 1)->orderBy('exact_date')->select()->results();
        $buttonList = [];
        if ($data[0]) {
            foreach ($data as $item) {
                $buttonList[] = ['title' => $this->getProcedureTitle($item) . ' ' . $item['exact_date'], 'id' => $item['id']];
            }
            TelegramKeyboard::$list = $buttonList;
            TelegramKeyboard::$id = 'id';
            TelegramKeyboard::$action = 'my_record';
            TelegramKeyboard::build();
            if ($this->tgParser::getCallbackByKey('a') == 'records_back') {
                $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['your_records'], TelegramKeyboard::get());
            } else {
                $this->tg->sendMessageWithInlineKeyboard($this->text['your_records'], TelegramKeyboard::get());
            }
        } else {
            $this->tg->sendMessage($this->text['no_records']);
        }
    }

    private function selectHowLong($check = false)
    {
        if ($check) {
            $this->db->table('recordList')->where('chatId', $this->chatId)->where('edit', 1)->update(['how_long_type' => $this->tgParser::getMessage() == 'Ближайшее время' ? 1 : 0]);
            $this->howLongTime();
        } else {
            $this->db->table('userList')->where('chatId', $this->chatId)->update(['mode' => 'how_long_rec']);
            $this->tg->sendMessageWithKeyboard($this->text['how_long'], [
                ['Ближайшее время'], ['На месяц вперед'], [$this->text['main_menu']]
            ]);
        }
    }

    private function howLongTime()
    {
        $this->db->table('userList')->where('chatId', $this->chatId)->update(['mode' => 'how_long_time_rec']);
        TelegramKeyboard::$list = $this->text['how_long_list'];
        TelegramKeyboard::$id = 'id';
        TelegramKeyboard::$action = 'how_long_rec';
        TelegramKeyboard::build();
        TelegramKeyboard::addButton($this->text['dont_remember'], ['a' => 'dont_remem']);
        $this->tg->removeKeyboard($this->text['select_how_long']);
        $this->tg->sendMessageWithInlineKeyboard('Список', TelegramKeyboard::get());
    }

    private function selectDay($check = false)
    {
        if ($check) {
            switch ($this->tgParser::getMessage()) {
                case 'Будний день':
                    $selected_day = 0;
                    $date = date('c', min(date(strtotime('next Thursday', time())), date(strtotime('next Tuesday', time()))));
                    if (date('l', time()) == 'Tuesday' || date('l', time()) == 'Thursday') {
                        $date = date('c', time());
                    }
                    $this->db->table('recordList')->where('chatId', $this->chatId)->where('edit', 1)->update(['start_search_date' => $date, 'selected_day' => $selected_day]);
                    $this->selectTime();
                    break;
                case 'Суббота':
                    $selected_day = 1;
                    $date = date('c', (date('l', time()) == 'Saturday' ? time() : strtotime('next Saturday', time())));
                    $this->db->table('recordList')->where('chatId', $this->chatId)->where('edit', 1)->update(['start_search_date' => $date, 'selected_day' => $selected_day]);
                    $this->selectTime();
                    break;
                case 'Неважно🏳':
                    $selected_day = 2;
                    $date = date('c', min(date(strtotime('next Tuesday', time())), date(strtotime('next Thursday', time())), date(strtotime('next Saturday', time()))));
                    $this->db->table('recordList')->where('chatId', $this->chatId)->where('edit', 1)->update(['start_search_date' => $date, 'selected_day' => $selected_day, 'selected_time' => 4]);
                    $this->getFreeRecords();
                    break;
            }
        } else {
            $this->db->table('userList')->where('chatId', $this->chatId)->update(['mode' => 'select_day_rec']);
            $this->tg->sendMessageWithKeyboard($this->text['select_day'], [
                ['Будний день'], ['Суббота'], ['Неважно🏳']
            ]);
        }
    }

    private function selectTime($check = false)
    {
        if ($check) {
            switch ($this->tgParser::getMessage()) {
                case 'Утро🌄':
                    $date = 1;
                    break;
                case 'День🏙':
                    $date = 2;
                    break;
                case 'Вечер🌇':
                    $date = 3;
                    break;
                case 'Неважно🏳':
                    $date = 4;
                    break;
            }
            if ($date) {
                $this->db->table('recordList')->where('chatId', $this->chatId)->where('edit', 1)->update(['selected_time' => $date]);
                $this->getFreeRecords();
            } else {
                $this->tg->sendMessage($this->text['wrong_time']);
            }
        } else {
            $this->db->table('userList')->where('chatId', $this->chatId)->update(['mode' => 'select_time_rec']);
            $this->tg->sendMessageWithKeyboard($this->text['select_time'], [
                ['Утро🌄'], ['День🏙'], ['Вечер🌇'], ['Неважно🏳']
            ]);
        }
    }

    private
    function getFreeRecords()
    {
        $google = new GoogleClient();
        $data = $this->db->table('recordList')->where('chatId', $this->chatId)->where('edit', 1)->select()->results();
        if ($data[0]['how_long_type'] === '0') {
            $this->db->table('recordList')->where('chatId', $this->chatId)->where('edit', 1)
                ->update(['start_search_date' => date('c', strtotime($data[0]['start_search_date'] . ' + 31 days'))]);
        }
        $data = $this->db->table('recordList')->where('chatId', $this->chatId)->where('edit', 1)->select()->results();
        $list = $google->getRecords($data[0]['start_search_date'], date('c', strtotime($data[0]['start_search_date'] . ' + 21 days')), $data);

        TelegramKeyboard::$id = 'id';
        TelegramKeyboard::$action = 'new_record';
        TelegramKeyboard::$columns = 2;
        if ($list) {
            TelegramKeyboard::$list = $list;
            TelegramKeyboard::build();
            $this->tg->removeKeyboard($this->text['free_time']);
        } else {
            $this->db->table('recordList')->where('chatId', $this->chatId)->where('edit', 1)->update(['selected_time' => 4]);
            $list = $google->getRecords($data[0]['start_search_date'], date('c', strtotime($data[0]['start_search_date'] . ' + 21 days')), $data);
            if ($list) {
                TelegramKeyboard::$list = $list;
                TelegramKeyboard::build();
                $this->tg->removeKeyboard($this->text['no_free_time']);
            } else {
                $this->db->table('recordList')->where('chatId', $this->chatId)->where('edit', 1)
                    ->update(['start_search_date' => date('c', strtotime($data[0]['start_search_date'] . ' + 31 days'))]);

                $data = $this->db->table('recordList')->where('chatId', $this->chatId)->where('edit', 1)->select()->results();
                $list = $google->getRecords($data[0]['start_search_date'], date('c', strtotime($data[0]['start_search_date'] . ' + 21 days')), $data);
                TelegramKeyboard::$list = $list;
                TelegramKeyboard::build();
                $this->tg->removeKeyboard($this->text['no_free_time']);
            }
        }
        $this->tg->sendMessageWithInlineKeyboard('Список', TelegramKeyboard::get());
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