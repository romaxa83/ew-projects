<?php

namespace App\Services\Dealership\Exception;

use App\Exceptions\ErrorsCode;
use Exception;

class DealershipException extends Exception
{
    // нет данных по отделу продаж
    public static function noDepartmentSalesData()
    {
        throw new self(__('error.dealership.not data for department sales'),ErrorsCode::BAD_REQUEST);
    }

    public static function noDepartmentServiceData()
    {
        throw new self(__('error.dealership.not data for department service'),ErrorsCode::BAD_REQUEST);
    }

    public static function noDepartmentCreditData()
    {
        throw new self(__('error.dealership.not data for department credit'),ErrorsCode::BAD_REQUEST);
    }

    public static function noDepartmentBodyData()
    {
        throw new self(__('error.dealership.not data for department body'),ErrorsCode::BAD_REQUEST);
    }
}
