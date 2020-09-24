<?php

namespace App\Commands;

class Address extends BaseCommand {

    function processCommand($par = false)
    {
        if ($this->tgParser::getMessage() == $this->text['public_transport']) {
            $this->tg->sendMessageWithKeyboard($this->text['public_transport_about'], [
                [$this->text['car_transport']],
                [$this->text['main_menu']]
            ]);
            $this->tg->sendMediaGroup([
                getenv('HTTP_HOST') . '/src/pub_transport0.jpg',
                getenv('HTTP_HOST') . '/src/pub_transport1.jpg',
                getenv('HTTP_HOST') . '/src/pub_transport2.jpg',
            ]);
            exit;
        } elseif ($this->tgParser::getMessage() == $this->text['car_transport']) {
            $this->tg->sendMessageWithKeyboard($this->text['car_transport_about'], [
                [$this->text['public_transport']],
                [$this->text['main_menu']]
            ]);
            exit;
        }
        $this->tg->sendMessageWithKeyboard($this->text['address'], [
            [$this->text['public_transport']],
            [$this->text['car_transport']],
            [$this->text['main_menu']]
        ]);
        $this->tg->sendMediaGroup([getenv('HTTP_HOST') . '/src/map.png']);
    }

}