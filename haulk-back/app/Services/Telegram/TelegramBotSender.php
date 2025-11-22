<?php

namespace App\Services\Telegram;

use TelegramBot\Api\Client;

class TelegramBotSender implements BotSender
{
    private $client;

    private $chatId;

    private $env;
    private $project;

    public function __construct(Client $client)
    {
        $this->client = $client;

        $config = config('telegram');
        $this->chatId = $config['chat_id'];
        $this->project = $config['project'];
        $this->env = $config['env'];

    }

    public function send(SendDataDto $dto): void
    {
        $type = $dto->type === SendDataDto::INFO
            ? "👀"
            : "⚠️"
        ;

        $message = $type . "<b> {$this->project} ({$this->env}) </b><i> ({$dto->username})</i>
<code>{$dto->msg}</code>
";
        if (!empty($dto->data)) {
        $message .= "\nData:\n";
        foreach ($dto->data as $key => $value) {
            $message .= "<b>{$key}</b>: {$value}\n";
        }
    }

        $this->client->sendMessage($this->chatId, $message, 'html');
    }
}
