<?php

namespace App\Console\Commands;

use App\Models\Orders\Order;
use App\Services\TimezoneService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FixOldTimeZones extends Command
{

    private const CONTACT_TYPE = [
        'pickup',
        'delivery',
        'shipper'
    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-old-timezones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix old timezones in order';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param TimezoneService $timezoneService
     * @return int
     */
    public function handle(TimezoneService $timezoneService): int
    {
        $usingTimeZones = $timezoneService->getTimezonesArr()->pluck('timezone')->toArray();

        $this->setInLog();

        foreach (self::CONTACT_TYPE as $value) {
            $orders = Order::whereNotIn($value . '_contact->timezone', $usingTimeZones)->get();

            $this->setInLog($orders->count() . ' items ' . strtoupper($value) . ' contacts have old timezone key.');

            if (!$orders->count()) {
                continue;
            }
            /**@var Order $order */
            foreach ($orders as $order) {
                $contact = $order->{$value . '_contact'};

                if (empty($contact['timezone'])) {
                    continue;
                }

                try {
                    $timezone = $timezoneService->changeOldTimeZoneFormatToNew($contact['timezone']);
                } catch (Exception $e) {
                    $this->setInLog('Couldn\'n found new timezone key for "' . $contact['timezone'] . '". Error: ' . $e->getMessage(), 'error');
                    continue;
                }

                if ($timezone['found'] === false) {
                    $this->setInLog('Couldn\'n found new timezone key for "' . $contact['timezone'] . '"', 'warn');
                    $this->setInLog('Recommended timezone: "'. $timezone['description'] .'"');
                    continue;
                }
                $this->setInLog('Order: ' . $order->id . '. Type: ' . $value . '. Old timezone: ' . $contact['timezone'] . '. New timezone: ' . $timezone['timezone']);

                $contact['timezone'] = $timezone['timezone'];

                $order->{$value . '_contact'} = $contact;
                $order->save();
            }
        }

        return 0;
    }

    private function setInLog(string $message = null, string $type = 'info')
    {
        $storage = Storage::disk('local');

        if ($message === null) {
            if ($storage->exists('timezone.log')) {
                $storage->delete('timezone.log');
                return;
            }
            return;
        }

        $storage->append('timezone.log', $message);

        switch ($type) {
            case 'error':
                $this->error($message);
                break;
            case 'warn':
                $this->warn($message);
                break;
            default:
                $this->info($message);
                break;
        }
    }
}
