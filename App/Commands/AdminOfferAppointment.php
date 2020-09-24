<?php

namespace App\Commands;

use App\TgHelpers\GoogleClient;
use App\TgHelpers\TelegramKeyboard;

class AdminOfferAppointment extends BaseCommand {

    function processCommand($par = false)
    {
        $action = $this->tgParser::getCallbackByKey('a');
        $id = $this->tgParser::getCallbackByKey('id');
        if ($action == 'appointment_admin') {
            switch ($id) {
                case 0:
                case 3:
                case 4:
                case 6:
                case 7:
                case 8:
                case 9:
                case 10:
                case 11:
                    $this->tg->deleteMessage($this->tgParser::getMsgId());
                    $this->db->table('adminRecordList')->insert(['procedure_id' => $id]);
                    $this->db->table('userList')->where('chatId', $this->chatId)->update(['recordId' => $this->db->lastInsertedId()]);
                    $this->writeFullName();
                    break;
                //Выравнивание
                case 1:
                    TelegramKeyboard::$list = [
                        ['title' => 'Брекеты', 'id' => 6],
                        ['title' => 'Элайнеры', 'id' => 7],
                        ['title' => 'Пластины', 'id' => 8],
                    ];
                    TelegramKeyboard::$id = 'id';
                    TelegramKeyboard::$action = 'appointment_admin';
                    TelegramKeyboard::build();
                    TelegramKeyboard::addButton($this->text['back'], ['a' => 'admin_app_back']);
                    $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['select_procedure'], TelegramKeyboard::get());
                    break;
                //Эстетическая стоматология
                case 2:
                    TelegramKeyboard::$list = [
                        ['title' => 'Отбеливание', 'id' => 9],
                        ['title' => 'Виниры', 'id' => 10],
                        ['title' => 'Реставрация', 'id' => 11],
                    ];
                    TelegramKeyboard::$id = 'id';
                    TelegramKeyboard::$action = 'appointment_admin';
                    TelegramKeyboard::build();
                    TelegramKeyboard::addButton($this->text['back'], ['a' => 'admin_app_back']);
                    $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['select_procedure'], TelegramKeyboard::get());
                    break;
                //Несколько процедур
                case 5:
                    TelegramKeyboard::$list = $this->text['few_services'];
                    TelegramKeyboard::$action = 'admin_few_app';
                    TelegramKeyboard::build();
                    TelegramKeyboard::addButton($this->text['back'], ['a' => 'admin_app_back']);
                    $this->db->table('adminRecordList')->insert(['procedure_id' => '']);
                    $this->db->table('userList')->where('chatId', $this->chatId)->update(['recordId' => $this->db->lastInsertedId()]);
                    $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['select_procedure'], TelegramKeyboard::get());
                    break;
            }
        } elseif ($action == 'admin_few_app') {
            $list = $this->text['few_services'];
            $procedureId = $this->tgParser::getCallbackByKey('id');

            $recordId = $this->userData['recordId'];
            $possibleDuplicate = $this->db->table('recordListProcedures')->where('recordId', $recordId)->where('procedureId', $procedureId)->select()->results();
            if ($possibleDuplicate[0]) {
                $this->db->query('DELETE FROM recordListProcedures WHERE recordId = ' . $recordId . ' AND procedureId = ' . $procedureId);
            } else {
                $this->db->table('recordListProcedures')->insert(['recordId' => $recordId, 'procedureId' => $procedureId]);
            }
            $proceduresIdList = $this->db->query('SELECT procedureId FROM recordListProcedures WHERE recordId = ' . $recordId);
            foreach ($list as $key => $item) {
                foreach ($proceduresIdList as $procedureId) {
                    if ($item['id'] == $procedureId['procedureId']) {
                        $list[$key]['title'] .= '✅';
                    }
                }
            }

            TelegramKeyboard::$list = $list;
            TelegramKeyboard::$id = 'id';
            TelegramKeyboard::$action = 'admin_few_app';
            TelegramKeyboard::build();
            TelegramKeyboard::addButton($this->text['ready'], ['a' => 'admin_few_app_ready', 'id' => $recordId]);
            TelegramKeyboard::addButton($this->text['back'], ['a' => 'admin_app_back']);
            $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['select_procedure'], TelegramKeyboard::get());
        } elseif ($action == 'admin_few_app_ready') {
            $recordId = $this->tgParser::getCallbackByKey('id');
            $proceduresIdList = $this->db->query('SELECT COUNT(*) AS count FROM recordListProcedures WHERE recordId = ' . $recordId);
            if ($proceduresIdList[0]['count'] >= 1) {
                $this->tg->deleteMessage($this->tgParser::getMsgId());
                $this->writeFullName();
            } else {
                $this->tg->showAlert($this->tgParser::getCallbackId(), $this->text['select_more']);
            }
        } elseif ($action == 'admin_how_long') {
            $this->db->table('adminRecordList')->where('id', $this->userData['recordId'])->update(['how_long' => $this->tgParser::getCallbackByKey('id')]);
            $this->selectDay();
        } else {
            switch ($this->userData['mode']) {
                case 'admin_fio':
                    $this->writeFullName(true);
                    break;
                case 'admin_phone_number':
                    $this->writePhoneNum(true);
                    break;
                case 'admin_select_day':
                    if ($this->tgParser::getMessage() == 'Посмотреть другие дни недели') {
                        $this->selectHowLong();
                    } else {
                        $this->selectDay(true);
                    }
                    break;
                case 'admin_select_time':
                    if ($this->tgParser::getMessage() == 'Посмотреть другие дни недели') {
                        $this->writeFullName();
                    } else {
                        $this->selectTime(true);
                    }
                    break;
                case 'admin_how_long':
                    $this->selectHowLong(true);
                    break;
                default:
                    TelegramKeyboard::$list = $this->text['service_list_actions'];
                    TelegramKeyboard::$action = 'appointment_admin';
                    TelegramKeyboard::build();
                    TelegramKeyboard::addButton($this->text['few_service_list'], ['a' => 'appointment_admin', 'id' => 5]);

                    if ($action == 'admin_app_back') {
                        $data = $this->db->table('recordList')->where('id', $this->userData['recordId'])->select()->results();
                        if ($data[0]) {
                            $this->db->query('DELETE FROM recordListProcedures WHERE recordId = ' . $data[0]['id']);
                            $this->db->query('DELETE FROM adminRecordList WHERE recordId = ' . $data[0]['id']);
                        }
                        $this->tg->updateMessageKeyboard($this->tgParser::getMsgId(), $this->text['select_procedure'], TelegramKeyboard::get());
                    } else {
                        $this->tg->sendMessageWithKeyboard($this->text['procedure_start'], [[$this->text['cancel']]]);
                        $this->tg->sendMessageWithInlineKeyboard($this->text['select_procedure'], TelegramKeyboard::get());
                    }
                    break;
            }
        }
    }

    private function writeFullName($check = false)
    {
        if ($check) {
            $fio = $this->tgParser::getMessage();
            if (strlen($fio) > 5) {
                $this->tg->deleteMessage($this->tgParser::getMsgId());
                $this->db->table('adminRecordList')->where('id', $this->userData['recordId'])->update(['fullName' => $fio]);
                $this->writePhoneNum();
            } else {
                $this->tg->sendMessage($this->text['wrong_full_name']);
            }
        } else {
            $this->db->table('userList')->where('chatId', $this->chatId)->update(['mode' => 'admin_fio']);
            $this->tg->deleteMessage($this->tgParser::getMsgId());
            $this->tg->sendMessageWithKeyboard($this->text['write_full_name'], [[$this->text['cancel']]]);
        }
    }

    private function writePhoneNum($check = false)
    {
        if ($check) {
            $phoneNumber = $this->tgParser::getMessage();
            if (strlen($phoneNumber) > 5) {
                $this->tg->deleteMessage($this->tgParser::getMsgId());
                $this->db->table('adminRecordList')->where('id', $this->userData['recordId'])->update(['phoneNumber' => $phoneNumber]);
                $this->selectHowLong();
            } else {
                $this->tg->sendMessage($this->text['wrong_phone_number']);
            }
        } else {
            $this->db->table('userList')->where('chatId', $this->chatId)->update(['mode' => 'admin_phone_number']);
            $this->tg->deleteMessage($this->tgParser::getMsgId());
            $this->tg->sendMessageWithKeyboard('Напишите номер телефона клиента📲', [[$this->text['cancel']]]);
        }
    }

    private function selectDay($check = false)
    {
        if ($check) {
            switch ($this->tgParser::getMessage()) {
                case 'Будний день':
                    $selected_day = 0;
                    $tuesday = date(strtotime('next Tuesday', time()));
                    $thursday = date(strtotime('next Thursday', time()));
                    $date = date('c', min($tuesday, $thursday));
                    if (date('l', time()) == 'Tuesday' || date('l', time()) == 'Thursday') {
                        $date = date('c', date(strtotime('today', time())));
                    }
                    $this->db->table('adminRecordList')->where('id', $this->userData['recordId'])->update(['start_search_date' => $date, 'selected_day' => $selected_day]);
                    $this->selectTime();
                    break;
                case 'Суббота':
                    $selected_day = 1;
                    $date = date('c', (date('l', time()) == 'Saturday' ? time() : strtotime('next Saturday', time())));
                    $this->db->table('adminRecordList')->where('id', $this->userData['recordId'])->update(['start_search_date' => $date, 'selected_day' => $selected_day]);
                    $this->selectTime();
                    break;
                case 'Неважно🏳':
                    $selected_day = 2;
                    $tuesday = date(strtotime('next Tuesday', time()));
                    $thursday = date(strtotime('next Thursday', time()));
                    $saturday = date(strtotime('next Saturday', time()));
                    $date = date('c', min($tuesday, $thursday, $saturday));
                    $this->db->table('adminRecordList')->where('id', $this->userData['recordId'])->update(['start_search_date' => $date, 'selected_day' => $selected_day, 'selected_time' => 4]);
                    $this->getFreeRecords();
                    break;
            }
        } else {
            $this->db->table('userList')->where('chatId', $this->chatId)->update(['mode' => 'admin_select_day']);
            $this->tg->sendMessageWithKeyboard($this->text['select_day'], [
                ['Будний день'], ['Суббота'], ['Неважно🏳'], [$this->text['cancel']]
            ]);
        }
    }

    private function selectHowLong($check = false)
    {
        if ($check) {
            $this->db->table('adminRecordList')->where('id', $this->userData['recordId'])->where('done', 0)->update(['how_long_type' => $this->tgParser::getMessage() == 'Ближайшее время' ? 1 : 0]);
            $this->howLongTime();
        } else {
            $this->db->table('userList')->where('chatId', $this->chatId)->update(['mode' => 'admin_how_long']);
            $this->tg->sendMessageWithKeyboard($this->text['how_long'], [
                ['Ближайшее время'], ['На месяц вперед'], [$this->text['cancel']]
            ]);
        }
    }

    private function howLongTime()
    {
        $this->db->table('userList')->where('chatId', $this->chatId)->update(['mode' => 'admin_how_long_time']);
        TelegramKeyboard::$list = $this->text['how_long_list'];
        TelegramKeyboard::$id = 'id';
        TelegramKeyboard::$action = 'admin_how_long';
        TelegramKeyboard::build();
        $this->tg->sendMessageWithKeyboard($this->text['select_how_long'], [[$this->text['cancel']]]);
        $this->tg->sendMessageWithInlineKeyboard('Список', TelegramKeyboard::get());
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
                $this->db->table('adminRecordList')->where('id', $this->userData['recordId'])->update(['selected_time' => $date]);
                $this->getFreeRecords();
            } else {
                $this->tg->sendMessage($this->text['wrong_time']);
            }
        } else {
            $this->db->table('userList')->where('chatId', $this->chatId)->update(['mode' => 'admin_select_time']);
            $this->tg->sendMessageWithKeyboard($this->text['select_time'], [
                ['Утро🌄'], ['День🏙'], ['Вечер🌇'], ['Неважно🏳'], [$this->text['cancel']]
            ]);
        }
    }

    private
    function getFreeRecords()
    {
        $google = new GoogleClient();
        $data = $this->db->table('adminRecordList')->where('id', $this->userData['recordId'])->select()->results();
        if ($data[0]['how_long_type'] === '0') {
            $this->db->table('adminRecordList')->where('id', $this->userData['recordId'])
                ->update(['start_search_date' => date('c', strtotime($data[0]['start_search_date'] . ' + 21 days'))]);
        }
        $data = $this->db->table('adminRecordList')->where('id', $this->userData['recordId'])->select()->results();
        $list = $google->getRecords($data[0]['start_search_date'], date('c', strtotime($data[0]['start_search_date'] . ' + 21 days')), $data);

        TelegramKeyboard::$id = 'id';
        TelegramKeyboard::$action = 'admin_new_record';
        TelegramKeyboard::$columns = 2;
        if ($list) {
            TelegramKeyboard::$list = $list;
            TelegramKeyboard::build();
            $this->tg->sendMessageWithKeyboard($this->text['free_time'], [[$this->text['cancel']], [$this->text['another_days_admin']]]);
        } else {
            $this->db->table('adminRecordList')->where('id', $this->userData['recordId'])->update(['selected_time' => 4]);
            $list = $google->getRecords($data[0]['start_search_date'], date('c', strtotime($data[0]['start_search_date'] . ' + 21 days')), $data);
            if ($list) {
                TelegramKeyboard::$list = $list;
                TelegramKeyboard::build();
                $this->tg->sendMessageWithKeyboard($this->text['no_free_time'], [[$this->text['cancel']], [$this->text['another_days_admin']]]);
            } else {
                $this->db->table('adminRecordList')->where('id', $this->userData['recordId'])
                    ->update(['start_search_date' => date('c', strtotime($data[0]['start_search_date'] . ' + 31 days'))]);

                $data = $this->db->table('adminRecordList')->where('id', $this->userData['recordId'])->select()->results();
                $list = $google->getRecords($data[0]['start_search_date'], date('c', strtotime($data[0]['start_search_date'] . ' + 21 days')), $data);
                TelegramKeyboard::$list = $list;
                TelegramKeyboard::build();
                $this->tg->sendMessageWithKeyboard($this->text['no_free_time'], [[$this->text['cancel']], [$this->text['another_days_admin']]]);
            }
        }
        $this->tg->sendMessageWithInlineKeyboard('Список', TelegramKeyboard::get());
    }


}