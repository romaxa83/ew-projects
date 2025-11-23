<?php

namespace WezomCms\TelegramBot;

use Illuminate\Http\Request;
use Throwable;

final class TelegramDto
{
    public const INFO = 'info';
    public const ERROR = 'error';

    private string $type;
    private null|string $message = null;
    private null|string $username = null;
    private string $env;
    private string $project;

    private null|string $errorMessage = null;
    private null|string $errorLocate = null;
    private null|string $area = null;

    private function __construct()
    {
        $this->env = config('cms.telegram-bot.bot.telegram_env');
        $this->project = config('cms.telegram-bot.bot.project');
    }

    public static function asInfo(string $message, null|string $userName): self
    {
        $self = new self();

        $self->type = self::INFO;
        $self->username = $userName;
        $self->message = $message;


        return $self;
    }

    public static function asError(Throwable $error, null|string $userName, null|Request $request = null): self
    {
        $self = new self();

        $self->type = self::ERROR;
        $self->username = $userName;
        $self->errorMessage = $error->getMessage() . ' ['. $error->getCode() .']';
        $self->errorLocate = $error->getFile() . ' ['. $error->getLine() .']';

        if($request){
            if(isApiRequest($request)){
                $self->area = "API";
            }
        }

        return $self;
    }

    public function getMessage(): string
    {
        if($this->type == self::INFO){
            return $this->messageInfo();
        }

        return $this->messageError();
    }

    private function header(): string
    {
        $msg = '';
        if($this->type == self::ERROR){
            $msg .= '⚠️ ️';
        }

        $msg .= "<b> {$this->project}::[ {$this->env} ";
        if($this->area){
            $msg .= "- {$this->area}";
        }

        $msg .= "]</b>";

        if($this->username){
            $msg .= " 👤 -  {$this->username}";
        }

        return $msg . PHP_EOL;
    }

    private function messageInfo(): string
    {
        return "{$this->header()} <code>{$this->message}</code>";
    }

    private function messageError(): string
    {
        return "{$this->header()}<code>{$this->errorMessage}</code>
------------------------------------------------------------------------------------------------
&#128269; {$this->errorLocate}
";
    }
}
