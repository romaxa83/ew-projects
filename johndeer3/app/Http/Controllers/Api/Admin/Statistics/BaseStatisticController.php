<?php

namespace App\Http\Controllers\Api\Admin\Statistics;

use App\Http\Controllers\Api\ApiController;
use App\Models\Report\Report;
use App\Services\Telegram\TelegramDev;
use App\Http\Request\Statistic;
use App\Type\ReportStatus;

class BaseStatisticController extends ApiController
{
    const ALL = 'all';

    public function __construct()
    {
        parent::__construct();
    }

    protected function requestStatusData($data)
    {
        if($data === self::ALL){
            $statuses = ReportStatus::list();
        } else {
            $statuses = parseParamsByComa($data);
        }

        return $statuses;
    }

    protected function requestCountryData($data, $year, $status)
    {
        if($data === self::ALL){
            $country = array_flip($this->countryData($year, $status));
        } else {
            $country = parseParamsByComa($data);
        }

        return $country;
    }

    protected function requestDeaelrData($data, $year, $status, $country)
    {
        if($data === self::ALL){
            $dealer = array_flip($this->dealerData($year, $status, $country));
        } else {
            $dealer = parseParamsByComa($data);
        }

        return $dealer;
    }

    protected function requestEgData($data, $dealer, $status, $year)
    {
        if($data === self::ALL){
            $eg = array_flip($this->egData($dealer, $status, $year));
        } else {
            $eg = parseParamsByComa($data);
        }

        return $eg;
    }

    protected function requestSizeData($value, $year, $status, $dealer, $eg, $md): array
    {
        if($value === self::ALL) {
            return array_flip($this->sizeData($year, $status, $dealer, $eg, $md));
        }

        return parseParamsByComa($value);
    }

    protected function requestCropData($value, $featureCrop, $year, $status, $dealer, $eg, $md): array
    {
        if($value === self::ALL) {
            return array_flip($this->cropData($featureCrop, $year, $status, $dealer, $eg, $md));
        }

        return parseParamsByComa($value);
    }

    protected function requestTypeData($value, $year, $status, $dealer, $eg, $md): array
    {
        if($value === self::ALL) {
            return array_flip($this->typeData($year, $status, $dealer, $eg, $md));
        }

        return parseParamsByComa($value);
    }

    protected function requestMdData($data, $dealer, $eg, $status, $year)
    {
        if($data === self::ALL){
            $md = array_flip($this->mdData($dealer, $eg, $status, $year));
        } else {
            $md = parseParamsByComa($data);
        }

        return $md;
    }

    // todo refactor - вынести в глобальный хелпер
    protected function prettyCountry(string $country): string
    {
        return trim(last(explode('-', $country)));
    }

    protected function finalView(array $data): array
    {
        $tmp = [];
        foreach ($data ?? [] as $id => $item){
            $tmp[$id] = $item['name'] . ' ('. $item['count'] .')';
        }

        return $tmp;
    }
}




