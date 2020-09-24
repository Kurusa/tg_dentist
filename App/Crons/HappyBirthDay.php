<?php

namespace App\Crons;
require_once('../../vendor/autoload.php');

use App\TgHelpers\TelegramApi;
use PHPtricks\Orm\Database;

$db = Database::connect();
$tg = new TelegramApi();
$userList = $db->query('SELECT birthday, chatId FROM userList WHERE birthday != ""');
if ($userList[0]) {
    foreach ($userList as $user) {
        if (date('m-d', strtotime(date('') . '+ 14 days')) == date('m-d', strtotime($user['birthday']))) {
            $tg->sendMessage('Добрый день! Мой 🤖чат-бот помнит, что через 2 недели у Вас день рождения!😀 🎉
В связи с этим, дарю Вам скидку 20% на чистку зубов (880 вместо 1100 ₴) 🎁
Воспользоваться можно в течение 2 недель🗓 , начиная с этого дня⏳🙂', $user['chatId']);
        }
    }
}
