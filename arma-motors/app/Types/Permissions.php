<?php

namespace App\Types;

final class Permissions
{
    const ROLE_GET = 'role.get';
    const ROLE_LIST = 'role.list';
    const ROLE_CREATE = 'role.create';
    const ROLE_EDIT = 'role.edit';
    const ROLE_DELETE = 'role.delete';

    const PERMISSION_LIST = 'permission.list';
    const PERMISSION_ATTACH = 'permission.attach';

    const ADMIN_CREATE = 'admin.create';
    const ADMIN_EDIT = 'admin.edit';
    const ADMIN_GET = 'admin.get';
    const ADMIN_LIST = 'admin.list';
    const ADMIN_CHANGE_STATUS = 'admin.change_status';
    const ADMIN_GENERATE_PASSWORD = 'admin.generate_password';
    const ADMIN_DELETE = 'admin.delete';
    const ADMIN_RESTORE = 'admin.restore';

    const USER_GET = 'user.get';
    const USER_LIST = 'user.list';
    const USER_MODERATION = 'user.moderation';
    const USER_EDIT = 'user.edit';
    const USER_DELETE = 'user.delete';
    const USER_RESTORE = 'user.restore';

    const USER_CAR_LIST = 'user.car.list';
    const USER_CAR_GET = 'user.car.get';
    const USER_CAR_MODERATE = 'user.car.moderate';
    const USER_CAR_RESTORE = 'user.car.restore';
    const USER_CAR_EDIT = 'user.car.edit';
    const USER_CAR_DELETE = 'user.car.delete';

    const CATALOG_CITY_EDIT = 'catalog.city.edit';
    const CATALOG_REGION_EDIT = 'catalog.region.edit';
    const CATALOG_BRAND_EDIT = 'catalog.brand.edit';
    const CATALOG_MODEL_EDIT = 'catalog.model.edit';
    const CATALOG_SERVICE_CREATE = 'catalog.service.create';
    const CATALOG_SERVICE_EDIT = 'catalog.service.edit';
    const CATALOG_OTHER_EDIT = 'catalog.other.edit';

    const DEALERSHIP_CREATE = 'dealership.create';
    const DEALERSHIP_EDIT = 'dealership.edit';

    const ARCHIVE_CAR_LIST = 'archive.car.list';
    const ARCHIVE_ADMIN_LIST = 'archive.admin.list';
    const ARCHIVE_USER_LIST = 'archive.user.list';
    const ARCHIVE_ORDER_LIST = 'archive.order.list';

    const ORDER_LIST = 'order.list';
    const ORDER_GET = 'order.get';
    const ORDER_EDIT = 'order.edit';
    const ORDER_DELETE = 'order.delete';
    const ORDER_RESTORE = 'order.restore';
    const ORDER_CAN_SEE = 'order.can.see';  // не супер админ ,но может видеть все заявки

    const PAGE_EDIT = 'page.edit';

    const SUPPORT_CATEGORY_CREATE = 'support.category.create';
    const SUPPORT_CATEGORY_EDIT = 'support.category.edit';
    const SUPPORT_MESSAGE_LIST = 'support.message.list';
    const SUPPORT_MESSAGE_GET = 'support.message.get';
    const SUPPORT_MESSAGE_EDIT = 'support.message.edit';
    const SUPPORT_MESSAGE_DELETE = 'support.message.delete';

    const CALC_CATALOG_CREATE = 'calc.catalog.create';
    const CALC_CATALOG_EDIT = 'calc.catalog.edit';
    const CALC_CATALOG_UPLOAD_SPARES = 'calc.catalog.upload.spares';
    const CALC_CATALOG_DELETE = 'calc.catalog.delete';

    const PROMOTION_CREATE = 'promotion.create';
    const PROMOTION_EDIT = 'promotion.edit';
    const PROMOTION_DELETE = 'promotion.delete';
    const PROMOTION_LIST = 'promotion.list';

    const LOYALTY_LIST = 'loyalty.list';
    const LOYALTY_GET = 'loyalty.get';
    const LOYALTY_CREATE = 'loyalty.create';
    const LOYALTY_EDIT = 'loyalty.edit';

    const CAR_ORDER_STATUS_EDIT = 'car-order-status.edit';
    const CAR_ORDER_STATUS_STATE_EDIT = 'car-order-status.edit-state';

}

