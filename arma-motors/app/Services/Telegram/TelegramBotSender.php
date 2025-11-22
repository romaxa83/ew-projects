<?php

namespace App\Services\Telegram;

use TelegramBot\Api\Client;

class TelegramBotSender implements TelegramBotSenderInterface
{
    private $chatId;
    private $config;

    private $env;
    private $project;

    public function __construct(private Client $client)
    {
        $this->config = config('telegram.develop');
        $this->chatId = $this->config['chat_id'];

        $this->project = env('APP_NAME');
        $this->env = $this->config['env'];

    }

    public function send(TelegramDTO $dto): void
    {

        $username = $dto->getUsername() ?? null;

        $message = "👀 INFO  <b> {$this->project} ({$this->env}) </b><i> ($username)</i>
&#128172;  <code>{$dto->getMessage()}</code>
";

        $this->client->sendMessage($this->chatId, $message, 'html');
    }

    public function error(TelegramDTO $dto): void
    {
        $username = $dto->getUsername() ?? '¯\_(ツ)_/¯';
        $locate = $dto->getLocate();
        $errorMessage = $dto->getErrorMessage();
        $errorLocate = $dto->getErrorLocate();

        $message = "❗😡  ERROR <b> {$this->project} ({$this->env})</b>
------------------------------------------------------------------------------------------------
🙋 {$username}
📌  {$locate}
&#128172;  <code>{$errorMessage}</code>
------------------------------------------------------------------------------------------------
&#128269; {$errorLocate}
";
        $this->client->sendMessage($this->chatId, $message, 'html');
    }
}
