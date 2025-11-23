<?php

namespace App\Console\Commands\Helpers\Communications\Ignore;

use App\Models\CommunicationsIgnoreList;
use Illuminate\Console\Command;

class AddValueToBlackList extends Command
{
    protected $signature = 'helpers:add_value_to_blacklist {--email=} {--phone=}';

    protected $description = 'Adding an email or phone number to the blacklist in the communication panel';

    protected array $divisionMisc = [];

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $start = microtime(true);

            $this->exec();

            $time = microtime(true) - $start;

            echo PHP_EOL;
            $this->info("Done [time = {$time}]");
            echo PHP_EOL;

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    public function exec()
    {
        if($email = $this->option('email')){
            $this->addEmail($email);
        }
        if($phone = $this->option('phone')){
            $this->addPhone($phone);
        }
    }

    private function addEmail(string $value): int
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->error("Значение '$value' не является действительным email-адресом.");
            return self::FAILURE;
        }


        if(CommunicationsIgnoreList::email()->where('value', $value)->exists()) {
            $this->error("Значение '$value' уже есть в бд.");
            return self::FAILURE;
        }

        CommunicationsIgnoreList::create([
            'value' => $value,
            'type' => CommunicationsIgnoreList::TYPE_EMAIL,
        ]);

        return self::SUCCESS;
    }

    private function addPhone(string $value): int
    {
        if (!is_numeric($value)) {
            $this->error("Значение '$value' не является действительным телефонм.");
            return self::FAILURE;
        }


        if(CommunicationsIgnoreList::phone()->where('value', $value)->exists()) {
            $this->error("Значение '$value' уже есть в бдю");
            return self::FAILURE;
        }

        CommunicationsIgnoreList::create([
            'value' => $value,
            'type' => CommunicationsIgnoreList::TYPE_PHONE,
        ]);

        return self::SUCCESS;
    }
}
