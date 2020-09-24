<?php

namespace App\Crons;
require_once('../../vendor/autoload.php');

use App\TgHelpers\TelegramApi;
use App\TgHelpers\TelegramKeyboard;
use PHPtricks\Orm\Database;

$db = Database::connect();
$tg = new TelegramApi();
$recordList = $db->query('SELECT * FROM recordList WHERE done = 1');
if ($recordList[0]) {
    foreach ($recordList as $record) {
        if (date('m-d', time()) == date('m-d', strtotime($record['exact_date'] . ' - 1 day'))) {
            TelegramKeyboard::addButton('Буду ✅', ['a' => 'will_be']);
            TelegramKeyboard::addButton('Не смогу ❌', ['a' => 'change_record_time', 'id' => $record['id']]);
            $tg->sendMessageWithInlineKeyboard('Добрый день! У Вас на завтра ' . $record['exact_date'] . ' назначен визит к стоматологу. Пожалуйста, подтвердите запись⬇',
                TelegramKeyboard::get(), $record['chatId']);

            $data = $db->query('SELECT phoneNumber FROM userList WHERE chatId = ' . $record['chatId']);
            $sms_text = urlencode('Здравствуйте, ждём Вас завтра на приём к стоматологу на ' . date('H:i', strtotime($record['exact_date'])));
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://api.sms.intel-tele.com/message/send/?username=Z-Dental&api_key=vwwLGtGgGRUgCtqJ&from=Dental&to=" . $data[0]['phoneNumber'] .
                "&message=$sms_text");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_exec($ch);
            curl_close($ch);

        }
    }
}

$adminRecordList = $db->query('SELECT * FROM adminRecordList WHERE done = 1');
if ($adminRecordList[0]) {
    foreach ($adminRecordList as $record) {
        if (date('m-d', time()) == date('m-d', strtotime($record['exact_date'] . ' - 1 day'))) {
            $sms_text = urlencode('Здравствуйте, ждём Вас завтра на приём к стоматологу на ' . date('H:i', strtotime($record['exact_date'])));
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://api.sms.intel-tele.com/message/send/?username=Z-Dental&api_key=vwwLGtGgGRUgCtqJ&from=Dental&to=" . $record['phoneNumber'] .
                "&message=$sms_text");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_exec($ch);
            curl_close($ch);

        }
    }
}
