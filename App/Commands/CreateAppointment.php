<?php

namespace App\Commands;

use App\TgHelpers\GoogleClient;
use App\TgHelpers\TelegramKeyboard;

class CreateAppointment extends BaseCommand {

    function processCommand($par = false)
    {
        if ($this->tgParser::getCallbackByKey('aid')) {
            $this->tg->deleteMessage($this->tgParser::getCallbackByKey('aid'));
        }

        $adminText = 'Новая запись✅' . "\n";
        $google = new GoogleClient();
        $this->tg->deleteMessage($this->tgParser::getMsgId());
        $this->db->table('recordList')->where('chatId', $this->chatId)->where('done', 0)->update(['exact_date' => $this->tgParser::getCallbackByKey('id')]);
        $data = $this->db->table('recordList')->where('chatId', $this->chatId)->where('done', 0)->select()->results();
        if ($data[0]['edit'] == '1') {
            $adminText = 'Перенос записи 🗓' . "\n";
            $google->delete($data[0]['event_id']);
        }
        $procedureTitle = '';
        $discount = false;
        if ($data[0]['procedure_id']) {
            foreach ($this->text['few_services'] as $service) {
                if ($data[0]['procedure_id'] == 3 && $this->userData['birthday']) {
                    if (date('m-d', strtotime(date('') . '+ 14 days')) == date('m-d', strtotime($this->userData['birthday']))) {
                        $discount = true;
                    }
                }
                if ($service['id'] == $data[0]['procedure_id']) {
                    $procedureTitle = mb_strtolower($service['no_title']);
                    break;
                }
            }
        } else {
            $proceduresIdList = $this->db->table('recordListProcedures')->where('recordId', $data[0]['id'])->select()->results();
            foreach ($this->text['few_services'] as $key => $item) {
                foreach ($proceduresIdList as $procedureId) {
                    if ($data[0]['procedure_id'] == 3 && $this->userData['birthday']) {
                        if (date('m-d', strtotime(date('') . '+ 14 days')) == date('m-d', strtotime($this->userData['birthday']))) {
                            $discount = true;
                        }
                    }
                    if ($item['id'] == $procedureId['procedureId']) {
                        $procedureTitle .= mb_strtolower($this->text['few_services'][$key]['no_title']) . ', ';
                    }
                }
            }

        }
        if ($discount) {
            if (date('m-d', strtotime(date('') . '+ 14 days')) >= date('m-d', strtotime($this->userData['birthday']))) {
                $adminText = 'Новая запись по акции - 20% на чистку✅' . "\n";
                $this->db->table('recordList')->where('id', $data[0]['id'])->update(['discount' => 1]);
            }
        }
        $adminText .= 'ФИО: ' . $this->userData['fullName']
            . "\n" . 'Телефон: ' . $this->userData['phoneNumber']
            . "\n" . 'Дата: ' . $data[0]['exact_date']
            . "\n" . 'Длительность: ' . $data[0]['how_long'] . ' мин.'
            . "\n" . 'Название услуги: ' . $procedureTitle;

        TelegramKeyboard::addButton('Отменить запись', ['a' => 'admin_cancel_confirm0', 'id' => $data[0]['id']]);
        $this->tg->sendMessageWithInlineKeyboard($adminText, TelegramKeyboard::get(), 205187375);

        $newUser = false;
        $previous = $this->db->table('recordList')->where('chatId', $this->chatId)->where('done', 0)->select()->results();
        if ($previous[0] || $this->userData['oldUser'] == '1') {
            $newUser = true;
        }
        $eventId = $google->create($this->userData['fullName'] . ' - ' . $this->userData['phoneNumber'] . ' - ' . $procedureTitle,
            date('c', strtotime($data[0]['exact_date'])), date('c', strtotime($data[0]['exact_date'] . ' + ' . $data[0]['how_long'] . ' minutes')),
            false, $newUser
        );

        $this->db->table('recordList')->where('chatId', $this->chatId)->where('done', 0)->update(['done' => 1, 'edit' => 0, 'event_id' => $eventId]);
        $this->triggerCommand(MainMenu::class, 'Вы записаны на <b>' . $procedureTitle . ' ' . $data[0]['exact_date'] . '</b>✅.
Если вдруг у Вас что-то поменяется или будете задерживаться, пожалуйста предупредите, так как запись очень плотная.
Хорошего дня!');
    }

}