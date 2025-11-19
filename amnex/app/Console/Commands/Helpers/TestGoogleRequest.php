<?php

namespace App\Console\Commands\Helpers;

use App\Services\Requests\Google\Map\Commands\GetDistanceBetweenAddresses;
use Illuminate\Console\Command;
use Throwable;

class TestGoogleRequest extends Command
{
    protected $signature = 'helpers:google-request';

    /**
     * @throws Throwable
     */
    public function handle(): int
    {
        $data = [
            'origin' => '6425 Penn Ave, Pittsburgh, PA 15206',
            'destination' => '76 9th Ave, New York, NY 10011',
        ];

        try {

            /** @var $command GetDistanceBetweenAddresses */
            $command = resolve(GetDistanceBetweenAddresses::class);
            $res = $command->handler($data);

            dd($res);

        } catch (Throwable $e) {
            dd($e);
            logger_info("SET DISTANCE BETWEEN ADDRESS FAIL", [$e->getMessage()]);
        }

        return static::SUCCESS;
    }
}
