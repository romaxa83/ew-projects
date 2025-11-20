<?php

namespace WezomCms\ServicesOrders\Types;

final class OrderStatus
{
    const CREATED  = 0; // создана в приложение, но не отправлена в 1с
    const RECEIVED = 1; // получена в 1с
    const IN_WORK  = 2; // в обработке в 1с
    const ACCEPTED = 3; // принята в 1с
    const DONE     = 4; // выполнена в 1с
    const REJECTED = 5; // отклонена в 1с

    const TYPE_PLANED = 'planed';
    const TYPE_COMPLETED = 'completed';

    private function __construct()
    {
    }

    public static function forCheck()
    {
        return [
            self::CREATED => __('cms-services-orders::admin.statuses.created'),
            self::RECEIVED => __('cms-services-orders::admin.statuses.received'),
            self::IN_WORK => __('cms-services-orders::admin.statuses.in_work'),
            self::ACCEPTED => __('cms-services-orders::admin.statuses.accepted'),
            self::DONE => __('cms-services-orders::admin.statuses.done'),
            self::REJECTED => __('cms-services-orders::admin.statuses.rejected'),
        ];
    }

    public static function forSelect()
    {
        return [
            self::CREATED => __('cms-services-orders::admin.statuses.created'),
            self::RECEIVED => __('cms-services-orders::admin.statuses.received'),
            self::IN_WORK => __('cms-services-orders::admin.statuses.in_work'),
            self::ACCEPTED => __('cms-services-orders::admin.statuses.accepted'),
            self::DONE => __('cms-services-orders::admin.statuses.done'),
        ];
    }

    public static function checkStatus($status): bool
    {
        return array_key_exists($status, self::forCheck());
    }

    public static function isCreated($status)
    {
        return $status == self::CREATED;
    }

    public static function isReceived($status)
    {
        return $status == self::RECEIVED;
    }

    public static function isInWork($status)
    {
        return $status == self::IN_WORK;
    }

    public static function isAccepted($status)
    {
        return $status == self::ACCEPTED;
    }

    public static function isDone($status)
    {
        return $status == self::DONE;
    }

    public static function isRejected($status)
    {
        return $status == self::REJECTED;
    }

    public static function renderStatus($status)
    {
        $html = '';
        if(self::isCreated($status) || self::isReceived($status)){
           $html = '<span class="badge badge-warning">'. self::forSelect()[$status] .'</span>';
        } elseif (self::isDone($status)){
            $html = '<span class="badge badge-success">'. self::forSelect()[$status] .'</span>';
        } elseif (self::isRejected($status)) {
            $html = '<span class="badge badge-danger">'. self::forSelect()[$status] .'</span>';
        } else {
            $html = '<span class="badge badge-info">'. self::forSelect()[$status] .'</span>';
        }

        return $html;
    }
}

