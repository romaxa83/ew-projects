<?php

namespace App\Console\Commands\Workers;

use App\Enums\DateFormatEnum;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Wezom\Quotes\Enums\QuoteStatusEnum;
use Wezom\Quotes\Models\Quote;
use Wezom\Quotes\Services\QuoteStatusService;
use Wezom\Settings\Models\Setting;

class TransferQuoteToExpire extends Command
{
    protected $signature = 'workers:transfer-quote-to-expire';

    public function handle(): int
    {
        logger_info('[worker] Set status EXPIRED START');

        try {
            $days = Setting::query()->where('key', Setting::KEY_DAYS_TO_EXPIRE)->first()?->value;

            if(is_null($days)){
                throw new \Exception('Not have days to expire');
            }

            // Перевод даты истечения из часовой зоны Чикаго в UTC
            $expiredDate = CarbonImmutable::now(DateFormatEnum::CLIENT_TZ->value)
                ->subDays((int)$days)
                ->setTimezone('UTC'); // Преобразование в UTC


            Quote::query()
                ->where('status', QuoteStatusEnum::NEW)
                ->where('quote_accepted_at', '<=', $expiredDate)
                ->each(function (Quote $quote) {
                    QuoteStatusService::setStatus($quote, QuoteStatusEnum::EXPIRED);
                    logger_info('[worker] Set status EXPIRED to quote [' . $quote->id . ']');

                });

        } catch (\Throwable $e) {
            logger_info('[worker] Set status EXPIRED FAIL', [$e]);

            return static::FAILURE;
        }

        logger_info('[worker] Set status EXPIRED FINISH');

        return static::SUCCESS;
    }
}
