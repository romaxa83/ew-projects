<?php

namespace App\Enums\Saas\GPS;

use App\Enums\BaseEnum;

/**
 * @method static static CREATE()               // создан
 * @method static static EDIT()                 // редактирование
 * @method static static EDIT_COMPANY()         // отредактирован компанией
 * @method static static EDIT_FLESPI()          // отредактирован флеспи
 * @method static static DELETE_FLESPI()        // удалено флеспи
 * @method static static ACTIVATE()             // активирован
 * @method static static ACTIVATE_TILL()        // девайс деактивирован, но еще работает, т.к. не закончен билинг период
 * @method static static ACTIVATE_TILL_UNPAID() // девайс деактивирован, но еще работает, запущен при неуплате за общую подписку
 * @method static static REMOVE_REQUEST_CLOSED()// удаление request_status = closed
 * @method static static INACTIVE()             // девайс полностью деактивирован
 * @method static static SUBSCRIPTION_RESTORE() // восстановлены после отмены подписки
 * @method static static SUBSCRIPTION_CANCEL()  // преобразованы после отмены подписки
 * @method static static PAYMENT_REC()          // создана запись, для дальнейшей оплаты
 * @method static static PAYMENT_REC_DELETE()   // запись по оплате удалена, был сформирован инвойс
 * @method static static ATTACH_TO_VEHICLE()    // назначен на технику
 * @method static static DETACH_TO_VEHICLE()    // снят с техники
 */

class DeviceHistoryContext extends BaseEnum
{
    public const CREATE               = 'create';
    public const EDIT                 = 'edit';
    public const EDIT_COMPANY         = 'edit_company';
    public const EDIT_FLESPI          = 'edit_flespi';
    public const DELETE_FLESPI        = 'delete_flespi';
    public const ACTIVATE             = 'activate';
    public const ACTIVATE_TILL        = 'activate_till';
    public const ACTIVATE_TILL_UNPAID = 'activate_till_unpaid';
    public const REMOVE_REQUEST_CLOSED = 'remove_request_closed';
    public const INACTIVE             = 'inactive';
    public const SUBSCRIPTION_RESTORE = 'subscription_restore';
    public const SUBSCRIPTION_CANCEL  = 'subscription_cancel';
    public const PAYMENT_REC          = 'payment_rec';
    public const PAYMENT_REC_DELETE   = 'payment_rec_delete';
    public const ATTACH_TO_VEHICLE    = 'attach_to_vehicle';
    public const DETACH_TO_VEHICLE    = 'detach_to_vehicle';
}
