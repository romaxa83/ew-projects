<?php

declare(strict_types=1);

namespace Wezom\Quotes\Services;

use App\Enums\DateFormatEnum;
use Carbon\CarbonImmutable;
use Wezom\Quotes\Enums\QuoteStatusEnum;
use Wezom\Quotes\Models\Quote;

final class QuoteAnalyticService
{
    public function simpleData(array $filter): array
    {
        $from = $filter['dateRange']->from ?? null;

        if ($from) {
            $fromUtc = CarbonImmutable::createFromFormat('Y-m-d', $from->format('Y-m-d'), DateFormatEnum::CLIENT_TZ->value)
                ->startOfDay()
                ->setTimezone('UTC');
        }

        $to = $filter['dateRange']->to ?? null;
        if ($to) {
            $toUtc = CarbonImmutable::createFromFormat('Y-m-d', $to->format('Y-m-d'), DateFormatEnum::CLIENT_TZ->value)
                ->endOfDay()
                ->setTimezone('UTC');
        }

        $count = Quote::query()
            ->when($from, fn ($query) => $query->where('created_at', '>=', $fromUtc))
            ->when($to, fn ($query) => $query->where('created_at', '<=', $toUtc))
            ->count();

        $countExpired = Quote::query()
            ->where('status', QuoteStatusEnum::EXPIRED)
            ->when($from, fn ($query) => $query->where('quote_accepted_at', '>=', $fromUtc))
            ->when($to, fn ($query) => $query->where('quote_accepted_at', '<=', $toUtc))
            ->count();
        $countProcessed = Quote::query()
            ->when($from, fn ($query) => $query->where('quote_accepted_at', '>=', $fromUtc))
            ->when($to, fn ($query) => $query->where('quote_accepted_at', '<=', $toUtc))
            ->whereNotIn('status', [
                QuoteStatusEnum::EXPIRED,
                QuoteStatusEnum::DRAFT,
                QuoteStatusEnum::NEW,
            ])
            ->count();

        return [
            'count_all' => $count,
            'count_expired' => $countExpired,
            'count_processed' => $countProcessed,
        ];
    }
}
