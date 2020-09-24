<?php

namespace App\Commands;

use App\TgHelpers\GoogleClient;

class AdminCreateAppointment extends BaseCommand {

    function processCommand($par = false)
    {
        $google = new GoogleClient();
        $this->tg->deleteMessage($this->tgParser::getMsgId());
        $this->db->table('adminRecordList')->where('id', $this->userData['recordId'])->update(['exact_date' => $this->tgParser::getCallbackByKey('id')]);
        $data = $this->db->table('adminRecordList')->where('id', $this->userData['recordId'])->select()->results();
        $procedureTitle = '';
        if ($data[0]['procedure_id']) {
            foreach ($this->text['few_services'] as $service) {
                if ($service['id'] == $data[0]['procedure_id']) {
                    $procedureTitle = mb_strtolower($service['no_title']);
                    break;
                }
            }
        } else {
            $proceduresIdList = $this->db->table('recordListProcedures')->where('recordId', $data[0]['id'])->select()->results();
            foreach ($this->text['few_services'] as $key => $item) {
                foreach ($proceduresIdList as $procedureId) {
                    if ($procedureId['procedureId'] == 3) {
                        $discount = true;
                    }
                    if ($item['id'] == $procedureId['procedureId']) {
                        $procedureTitle .= mb_strtolower($this->text['few_services'][$key]['no_title']) . ', ';
                    }
                }
            }

        }
        $text = 'Вы записали ' . $data[0]['fullName'] . ' <b>' . $procedureTitle . ' на ' . $data[0]['exact_date'] . '</b>✅.';

        $sms_text = urlencode('Вы записаны на приём к стоматологу ' . date('Y.m.d', strtotime($data[0]['exact_date'])) . ' на ' . date('H:i', strtotime($data[0]['exact_date'])) . '. +380979317808');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://api.sms.intel-tele.com/message/send/?username=Z-Dental&api_key=vwwLGtGgGRUgCtqJ&from=Dental&to=" . $data[0]['phoneNumber'] . "&message=$sms_text");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);
        curl_close($ch);

        $eventId = $google->create($data[0]['fullName'] . ' - ' . $data[0]['phoneNumber'] . ' - ' . $procedureTitle,
            date('c', strtotime($data[0]['exact_date'])), date('c', strtotime($data[0]['exact_date'] . ' + ' . $data[0]['how_long'] . ' minutes')), true);
        $this->db->table('adminRecordList')->where('id', $this->userData['recordId'])->update(['done' => 1, 'edit' => 0, 'event_id' => $eventId]);
        $this->triggerCommand(MainMenu::class, $text);
    }

}