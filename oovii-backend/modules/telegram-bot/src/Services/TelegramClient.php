<?php

namespace WezomCms\TelegramBot\Services;

use TelegramBot\Api\Client;
use WezomCms\TelegramBot\TelegramDto;

final class TelegramClient
{
    private static $instance;

    private Client $client;
    private $chatId;

    private function __construct()
    {
         $this->client = new Client(config('cms.telegram-bot.bot.telegram_token'));
         $this->chatId = config('cms.telegram-bot.bot.telegram_chat_id');
    }

    protected function __clone(){}

    /**
     * @throws \Exception
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public static function getInstance(): self
    {
        if (is_null(static::$instance)) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    public static function send(TelegramDto $dto)
    {
        $sender = static::getInstance();

        $sender->client->sendMessage($sender->chatId, $dto->getMessage(), 'html');
    }
}
