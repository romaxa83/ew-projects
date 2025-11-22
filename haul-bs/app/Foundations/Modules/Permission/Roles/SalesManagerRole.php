<?php

namespace App\Foundations\Modules\Permission\Roles;

use App\Foundations\Modules\Permission\Models\Role as RoleModel;
use App\Foundations\Modules\Permission;

final readonly class SalesManagerRole extends BaseRole
{
    public const NAME = 'sales_manager';
    public const GUARD = RoleModel::GUARD_USER;

    public function getPermissions(): array
    {
        return [
            Permission\Permissions\User\UserPermissionsGroup::class => [
                Permission\Permissions\User\UserShortListReadPermission::class,
            ],
            Permission\Permissions\Profile\ProfilePermissionsGroup::class => [
                Permission\Permissions\Profile\ProfileReadPermission::class,
                Permission\Permissions\Profile\ProfileUpdatePermission::class,
            ],
            Permission\Permissions\VehicleOwner\VehicleOwnerPermissionsGroup::class => [
                Permission\Permissions\VehicleOwner\VehicleOwnerCreateCustomerPermission::class,
                Permission\Permissions\VehicleOwner\VehicleOwnerCreatePermission::class,
                Permission\Permissions\VehicleOwner\VehicleOwnerDeleteCustomerPermission::class,
                Permission\Permissions\VehicleOwner\VehicleOwnerDeletePermission::class,
                Permission\Permissions\VehicleOwner\VehicleOwnerReadCustomerPermission::class,
                Permission\Permissions\VehicleOwner\VehicleOwnerReadOwnerPermission::class,
                Permission\Permissions\VehicleOwner\VehicleOwnerReadPermission::class,
                Permission\Permissions\VehicleOwner\VehicleOwnerUpdateCustomerPermission::class,
                Permission\Permissions\VehicleOwner\VehicleOwnerUpdatePermission::class,
            ],
            Permission\Permissions\Inventory\Inventory\InventoryPermissionsGroup::class => [
                Permission\Permissions\Inventory\Inventory\InventoryReadPermission::class,
            ],
            Permission\Permissions\Tag\TagPermissionsGroup::class => [
                Permission\Permissions\Tag\TagReadPermission::class,
            ],
            Permission\Permissions\Order\Parts\OrderPermissionsGroup::class => [
                Permission\Permissions\Order\Parts\OrderReadPermission::class,
                Permission\Permissions\Order\Parts\OrderCreatePermission::class,
                Permission\Permissions\Order\Parts\OrderUpdatePermission::class,
                Permission\Permissions\Order\Parts\OrderDraftDeletePermission::class,
                Permission\Permissions\Order\Parts\OrderChangeStatusPermission::class,
                Permission\Permissions\Order\Parts\OrderAddCommentPermission::class,
                Permission\Permissions\Order\Parts\OrderDeleteCommentPermission::class,
                Permission\Permissions\Order\Parts\OrderAssignSalesManagerPermission::class,
                Permission\Permissions\Order\Parts\OrderRefundedPermission::class,
                Permission\Permissions\Order\Parts\OrderGenerateInvoicePermission::class,
                Permission\Permissions\Order\Parts\OrderCreatePaymentPermission::class,
                Permission\Permissions\Order\Parts\OrderSendDocumentPermission::class,
                Permission\Permissions\Order\Parts\OrderSendPaymentLinkPermission::class,
            ],
            Permission\Permissions\Inventory\Category\CategoryPermissionsGroup::class => [
                Permission\Permissions\Inventory\Category\CategoryReadPermission::class,
            ],
            Permission\Permissions\Inventory\Brand\BrandPermissionsGroup::class => [
                Permission\Permissions\Inventory\Brand\BrandReadPermission::class,
            ],
            Permission\Permissions\Inventory\FeatureValue\FeatureValuePermissionsGroup::class => [
                Permission\Permissions\Inventory\FeatureValue\FeatureValueReadPermission::class,
            ],
            Permission\Permissions\Inventory\Feature\FeaturePermissionsGroup::class => [
                Permission\Permissions\Inventory\Feature\FeatureReadPermission::class,
            ],
            Permission\Permissions\Inventory\Unit\UnitPermissionsGroup::class => [
                Permission\Permissions\Inventory\Unit\UnitReadPermission::class,
            ],
        ];
    }
}
