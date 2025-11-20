<?php

namespace WezomCms\Services\Types;

final class ServiceType
{
    const TYPE_STO        = 1;
    const TYPE_REPAIRS    = 2;
    const TYPE_INSURANCE  = 3;
    const TYPE_SPARES     = 5;
    const TYPE_TRADE_IN   = 4;
    const TYPE_TEST_DRIVE = 6;

    protected $type;

    private function __construct($type)
    {
        $this->type = $type;
    }

    public function type()
    {
        return $this->type;
    }

    private static function checkType($type)
    {
        return self::TYPE_STO == $type
            || self::TYPE_INSURANCE == $type
            || self::TYPE_REPAIRS == $type
            || self::TYPE_SPARES == $type
            || self::TYPE_TRADE_IN == $type
            || self::TYPE_TEST_DRIVE == $type;
    }

    // можно ли для данного типы сервиса запросить свободное время
    public static function getFreeTime($type)
    {
        return self::isSto($type) || self::isTestDrive($type);
    }

    public static function isInsurance($type)
    {
        return $type == self::TYPE_INSURANCE;
    }

    public static function isSto($type)
    {
        return $type == self::TYPE_STO;
    }

    public static function isTestDrive($type)
    {
        return $type == self::TYPE_TEST_DRIVE;
    }

    public static function getTypeBy($type)
    {
        if(!self::checkType($type)){
            throw new \Exception(__('cms-services::site.exception.data type of service does not exist'));
        }

        return new self($type);
    }

    public static function getTypeSto()
    {
        return new self(self::TYPE_STO);
    }

    public static function getTypeInsurance()
    {
        return new self(self::TYPE_INSURANCE);
    }
}

