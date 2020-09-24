<?php

namespace App;

use App\Commands\MainMenu;
use App\TgHelpers\TelegramApi;
use PHPtricks\Orm\Database;

class WebhookController {

    public function run()
    {
        $update = \json_decode(file_get_contents('php://input'), TRUE);
        $isCallback = !array_key_exists('message', $update);
        $response = $isCallback ? $update['callback_query'] : $update;
        $chatId = $response['message']['chat']['id'];

        $unknownCommand = true;
        if ($isCallback) {
            $config = include('config/callback_commands.php');
            $action = \json_decode($response['data'], true)['a'];

            if (isset($config[$action])) {
                (new $config[$action]($response))->handle($response);
            }

            $tg = new TelegramApi();
            $tg->answerCallbackQuery($response['id']);
        } else {
            $db = Database::connect();
            if ($update['message']['text']) $db->table('messageList')->insert(['chatId' => $chatId, 'text' => $update['message']['text'], 'date' => time()]);

            // checking commands -> keyboard commands -> mode -> exit
            if ($update['message']['text']) {
                $text = $update['message']['text'];
                $key = $text;

                if (strpos($text, '/') === 0) {
                    $handlers = include('config/slash_commands.php');
                } else {
                    $handlers = include('config/keyboard_commands.php');
                }

                if (isset($handlers[$key])) {
                    (new $handlers[$key]($update))->handle($update);
                    exit;
                } else {
                    $handlers = include('config/mode_commands.php');
                    $user = $db->table('userList')->where('chatId', $chatId)->select(['mode'])->results();
                    if ($user[0] && $handlers[$user[0]['mode']]) {
                        (new $handlers[$user[0]['mode']]($update))->handle($update);
                        exit;
                    }
                }
            }
        }

        if ($unknownCommand) {
            (new MainMenu())->handle($update);
        }
    }

}

