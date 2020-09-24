<?php

namespace App\TgHelpers;

class TelegramApi {

    public $result;
    public $chatId;
    public $curl;

    public $API = 'https://api.telegram.org/bot';

    public function __construct()
    {
        $this->curl = curl_init();
    }

    public function api($method, $params)
    {
        // TODO move telegram api key
        $url = $this->API . '989839754:AAGt0g5FmHOdzap5HzK9ZxWjqpk1aUW-XrA' . '/' . $method;

        return $this->do($url, $params);
    }

    private function do(string $url, array $params = []): ?array
    {
        $params = json_encode($params);

        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json', 'Content-Length: ' . strlen($params),
        ]);

        $this->result = json_decode(curl_exec($this->curl), TRUE);
        return $this->result;
    }

    public function showAlert(int $callbackQueryId, string $text)
    {
        $this->api('answerCallbackQuery', [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'showAlert' => true,
        ]);
    }

    public function isTyping()
    {
        $this->api('sendChatAction', ['chat_id' => $this->chatId, 'action' => 'typing']);
    }

    public function sendMessage(string $text, $chatId = null)
    {
        $this->isTyping();
        $this->api('sendMessage', [
            'chat_id' => $chatId ? $chatId : $this->chatId, 'text' => $text, 'parse_mode' => 'HTML',
        ]);
    }

    public function sendMediaGroup(array $mediaGroup)
    {
        $mediaResult = [];
        foreach ($mediaGroup as $media) {
            $mediaResult[] = [
                'type' => 'photo',
                'media' => $media,
            ];
        }

        return $this->api('sendMediaGroup', [
            'chat_id' => $this->chatId,
            'media' => json_encode($mediaResult)
        ]);
    }

    public function removeKeyboard(string $text)
    {
        $this->api('sendMessage', [
            'chat_id' => $this->chatId,
            'text' => $text,
            'reply_markup' => [
                'remove_keyboard' => true,
            ],
            'parse_mode' => 'Markdown',
        ]);
    }

    public function sendMessageWithKeyboard(string $text, array $encodedMarkup, $chatId = null)
    {
        $this->isTyping();
        $this->api('sendMessage', [
            'chat_id' => $chatId ? $chatId : $this->chatId, 'text' => $text,
            'reply_markup' => [
                'keyboard' => $encodedMarkup,
                'one_time_keyboard' => false,
                'resize_keyboard' => true,
            ], 'parse_mode' => 'HTML',
        ]);
    }

    public function sendMessageWithInlineKeyboard(string $text, $buttons, $chatId = null)
    {
        $this->isTyping();
        $this->api('sendMessage', [
            'chat_id' => $chatId ? $chatId : $this->chatId,
            'reply_markup' => [
                'inline_keyboard' => $buttons,
            ],
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    public function answerCallbackQuery($callbackQueryId)
    {
        $this->api('answerCallbackQuery', [
            'callback_query_id' => $callbackQueryId,
        ]);
    }

    public function deleteMessage(int $messageId)
    {
        $this->api('deleteMessage', [
            'chat_id' => $this->chatId, 'message_id' => $messageId,
        ]);
    }

    public function updateMessageKeyboard(int $messageId, string $newText, array $newButton)
    {
        $this->api('editMessageText', [
            'chat_id' => $this->chatId,
            'message_id' => $messageId, 'text' => $newText, 'reply_markup' => [
                'inline_keyboard' => $newButton,
            ], 'parse_mode' => 'HTML',
        ]);
    }

    public function __destruct()
    {
        $this->curl = curl_close($this->curl);
    }

}