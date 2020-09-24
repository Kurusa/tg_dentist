<?php

namespace App\Crons;
require_once('../../vendor/autoload.php');

use App\TgHelpers\TelegramApi;
use PHPtricks\Orm\Database;

$db = Database::connect();
$tg = new TelegramApi();
$recordList = $db->query('SELECT chatId FROM recordList AS RL WHERE exact_date < DATE_SUB(NOW(), INTERVAL 6 MONTH) AND (SELECT notification FROM userList WHERE chatId = RL.chatId) = 1');
foreach ($recordList as $record) {
    $tg->sendMessageWithKeyboard('Добрый день! Последний раз Вы были у стоматолога полгода назад🗓. Предлагаю Вам записаться на профилактическую чистку🚿.
Профилактика позволяет выявить уже имеющиеся и только зарождающиеся проблемы, которые легко решить на ранней стадии.', [
        ['Записаться на прием🗓🖊'], ['Отписаться от рассылки'], ['Главное меню ✅']
    ], $record['chatId']);
}
