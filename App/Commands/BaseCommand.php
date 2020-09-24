<?php

namespace App\Commands;

use App\Services\QuizApiService;
use App\TgHelpers\TelegramParser;
use App\TgHelpers\TelegramApi;
use PHPtricks\Orm\Database;

abstract class BaseCommand {

    /**
     * @var TelegramParser
     */
    protected $tgParser;

    /**
     * @var Database
     */
    protected $db;

    /**
     * @var TelegramApi
     */
    protected $tg;

    protected $userData;
    protected $chatId;
    protected $text;

    private $update;

    function handle(array $update, $par = false)
    {
        $this->update = $update;
        $this->db = Database::connect();
        $this->tgParser = new TelegramParser($update);
        $this->tg = new TelegramApi();
        $this->tg->chatId = $this->chatId = $this->tgParser::getChatId();

        $data = $this->db->table('userList')->where('chatId', $this->chatId)->select()->results();
        $this->userData = $data[0] ? $data[0] : [];
        $this->text = include(SITE_ROOT . '/App/config/lang.php');

        if ($this->userData['isBlockedByBot'] == '1') {
            if ($this->tgParser::getMessage() == $this->text['contact_us']) {
                $this->processCommand($par ? $par : '');
            }
            exit;
        }

        $this->processCommand($par ? $par : '');

    }

    function triggerCommand($class, $par = false)
    {
        (new $class())->handle($this->update, $par);
    }

    abstract function processCommand($par = false);

}