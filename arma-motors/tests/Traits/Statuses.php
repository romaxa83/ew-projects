<?php

namespace Tests\Traits;

trait Statuses
{
    public $admin_status_active = 'active';
    public $admin_status_inactive = 'inactive';

    public $user_status_draft = 'DRAFT';
    public $user_status_active = 'ACTIVE';
    public $user_status_verify = 'VERIFY';

    public $user_car_status_draft = 'DRAFT';
    public $user_car_status_moderate = 'MODERATE';
    public $user_car_status_verify = 'VERIFY';

    public $user_image_type_passport = 'PASSPORT';
    public $user_image_type_avatar = 'AVATAR';

    public $translation_app = 'APP';
    public $translation_admin = 'ADMIN';

    public $image_model_user = 'user';
    public $image_model_admin = 'admin';
    public $image_model_dealership = 'dealership';
    public $image_model_brand = 'brand';
    public $image_model_model = 'model';
    public $image_model_car = 'car';

    public $department_type_credit = 'credit';
    public $department_type_service = 'service';
    public $department_type_sales = 'sales';
    public $department_type_body = 'body';

    public $order_status_draft = 'DRAFT';
    public $order_status_created = 'CREATED';
    public $order_status_in_process = 'IN_PROCESS';
    public $order_status_done = 'DONE';
    public $order_status_close = 'CLOSE';
    public $order_status_reject = 'REJECT';

    public $order_payment_status_none = 'NONE';
    public $order_payment_status_not = 'NOT';
    public $order_payment_status_part = 'PART';
    public $order_payment_status_full = 'FULL';

    public $support_message_status_draft = 'DRAFT';
    public $support_message_status_read = 'READ';
    public $support_message_status_done = 'DONE';

    public $type_spares_group_qty = 'qty';
    public $type_spares_group_volume = 'volume';

    public $order_period_all = 'all';
    public $order_period_today = 'today';
    public $order_period_incoming = 'incoming';

    public $order_type_ordinary = 'ordinary';
    public $order_type_recommend = 'recommend';
    public $order_type_argeement = 'agreement';

}
