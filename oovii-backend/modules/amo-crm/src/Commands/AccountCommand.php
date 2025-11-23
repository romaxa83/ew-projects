<?php

namespace WezomCms\AmoCrm\Commands;

use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use Illuminate\Console\Command;
use WezomCms\AmoCrm\Services\AmoCrmService;

class AccountCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amo-crm:account';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @param  AmoCrmService  $service
     * @return void
     */
    public function handle(AmoCrmService $service)
    {
        try {
            $accountInfo = $service->getAccountInfo();

            $rows = [];
            foreach (['id', 'name', 'subdomain', 'currentUserId', 'version'] as $field) {
                $method = 'get' . ucfirst($field);
                if (method_exists($accountInfo, $method)) {
                    $rows[] = [$field, $accountInfo->$method()];
                }
            }
            $this->table([], $rows);
        } catch (AmoCRMoAuthApiException $e) {
            $this->error('Failed to authorize');
        } catch (AmoCRMApiException $e) {
            $this->error($e->getMessage());
        }
    }
}
