<?php

namespace App\Foundations\Modules\Permission\Roles;

use App\Foundations\Modules\Permission;
use App\Foundations\Modules\Permission\Models\Role as RoleModel;

final readonly class AdminRole extends BaseRole
{
    public const NAME = 'admin';

    public const GUARD = RoleModel::GUARD_USER;

    public function getPermissions(): array
    {
        return [
            Permission\Permissions\User\UserPermissionsGroup::class => [
                Permission\Permissions\User\UserReadPermission::class,
                Permission\Permissions\User\UserShortListReadPermission::class,
                Permission\Permissions\User\UserCreatePermission::class,
                Permission\Permissions\User\UserUpdatePermission::class,
                Permission\Permissions\User\UserDeletePermission::class,
            ],
            Permission\Permissions\VehicleOwner\VehicleOwnerPermissionsGroup::class => [
                Permission\Permissions\VehicleOwner\VehicleOwnerAddCommentPermission::class,
                Permission\Permissions\VehicleOwner\VehicleOwnerDeleteCommentPermission::class,
                Permission\Permissions\VehicleOwner\VehicleOwnerCreatePermission::class,
                Permission\Permissions\VehicleOwner\VehicleOwnerDeletePermission::class,
                Permission\Permissions\VehicleOwner\VehicleOwnerReadPermission::class,
                Permission\Permissions\VehicleOwner\VehicleOwnerUpdatePermission::class,
                Permission\Permissions\VehicleOwner\VehicleOwnerReadOwnerPermission::class,
                Permission\Permissions\VehicleOwner\VehicleOwnerReadCustomerPermission::class,
                Permission\Permissions\VehicleOwner\VehicleOwnerCreateCustomerPermission::class,
                Permission\Permissions\VehicleOwner\VehicleOwnerDeleteCustomerPermission::class,
                Permission\Permissions\VehicleOwner\VehicleOwnerUpdateCustomerPermission::class,
            ],
            Permission\Permissions\TypeOfWork\TypeOfWorkPermissionsGroup::class => [
                Permission\Permissions\TypeOfWork\TypeOfWorkCreatePermission::class,
                Permission\Permissions\TypeOfWork\TypeOfWorkDeletePermission::class,
                Permission\Permissions\TypeOfWork\TypeOfWorkUpdatePermission::class,
                Permission\Permissions\TypeOfWork\TypeOfWorkReadPermission::class,
            ],
            Permission\Permissions\Truck\TruckPermissionsGroup::class => [
                Permission\Permissions\Truck\TruckCreatePermission::class,
                Permission\Permissions\Truck\TruckUpdatePermission::class,
                Permission\Permissions\Truck\TruckReadPermission::class,
                Permission\Permissions\Truck\TruckDeletePermission::class,
                Permission\Permissions\Truck\TruckAddCommentPermission::class,
                Permission\Permissions\Truck\TruckDeleteCommentPermission::class,
            ],
            Permission\Permissions\Trailer\TrailerPermissionsGroup::class => [
                Permission\Permissions\Trailer\TrailerCreatePermission::class,
                Permission\Permissions\Trailer\TrailerUpdatePermission::class,
                Permission\Permissions\Trailer\TrailerReadPermission::class,
                Permission\Permissions\Trailer\TrailerDeletePermission::class,
                Permission\Permissions\Trailer\TrailerAddCommentPermission::class,
                Permission\Permissions\Trailer\TrailerDeleteCommentPermission::class,
            ],
            Permission\Permissions\Tag\TagPermissionsGroup::class => [
                Permission\Permissions\Tag\TagReadPermission::class,
                Permission\Permissions\Tag\TagCreatePermission::class,
                Permission\Permissions\Tag\TagUpdatePermission::class,
                Permission\Permissions\Tag\TagDeletePermission::class,
            ],
            Permission\Permissions\Supplier\SupplierPermissionsGroup::class => [
                Permission\Permissions\Supplier\SupplierReadPermission::class,
                Permission\Permissions\Supplier\SupplierCreatePermission::class,
                Permission\Permissions\Supplier\SupplierUpdatePermission::class,
                Permission\Permissions\Supplier\SupplierDeletePermission::class,
            ],
            Permission\Permissions\Setting\SettingPermissionsGroup::class => [
                Permission\Permissions\Setting\SettingReadPermission::class,
                Permission\Permissions\Setting\SettingUpdatePermission::class,
            ],
            Permission\Permissions\Role\RolePermissionsGroup::class => [
                Permission\Permissions\Role\RoleMechanicPermission::class,
                Permission\Permissions\Role\RoleSalesManagerPermission::class,
            ],
            Permission\Permissions\Report\ReportPermissionsGroup::class => [
                Permission\Permissions\Report\ReportOrderPermission::class,
                Permission\Permissions\Report\ReportInventoryPermission::class,
            ],
            Permission\Permissions\Profile\ProfilePermissionsGroup::class => [
                Permission\Permissions\Profile\ProfileReadPermission::class,
                Permission\Permissions\Profile\ProfileUpdatePermission::class,
            ],
            Permission\Permissions\Order\BS\OrderPermissionsGroup::class => [
                Permission\Permissions\Order\BS\OrderReadPermission::class,
                Permission\Permissions\Order\BS\OrderCreatePermission::class,
                Permission\Permissions\Order\BS\OrderUpdatePermission::class,
                Permission\Permissions\Order\BS\OrderAddCommentPermission::class,
                Permission\Permissions\Order\BS\OrderDeleteCommentPermission::class,
                Permission\Permissions\Order\BS\OrderChangeStatusPermission::class,
                Permission\Permissions\Order\BS\OrderCreatePaymentPermission::class,
                Permission\Permissions\Order\BS\OrderDeletePaymentPermission::class,
                Permission\Permissions\Order\BS\OrderDeletePermanentlyPermission::class,
                Permission\Permissions\Order\BS\OrderGenerateInvoicePermission::class,
                Permission\Permissions\Order\BS\OrderReassignMechanicPermission::class,
                Permission\Permissions\Order\BS\OrderReportPermission::class,
                Permission\Permissions\Order\BS\OrderRestorePermission::class,
                Permission\Permissions\Order\BS\OrderSendDocumentPermission::class,
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
                Permission\Permissions\Order\Parts\OrderDeletePaymentPermission::class,
                Permission\Permissions\Order\Parts\OrderSendDocumentPermission::class,
                Permission\Permissions\Order\Parts\OrderSendPaymentLinkPermission::class,
            ],
            Permission\Permissions\Inventory\Category\CategoryPermissionsGroup::class => [
                Permission\Permissions\Inventory\Category\CategoryCreatePermission::class,
                Permission\Permissions\Inventory\Category\CategoryUpdatePermission::class,
                Permission\Permissions\Inventory\Category\CategoryDeletePermission::class,
                Permission\Permissions\Inventory\Category\CategoryReadPermission::class,
            ],
            Permission\Permissions\Inventory\Brand\BrandPermissionsGroup::class => [
                Permission\Permissions\Inventory\Brand\BrandCreatePermission::class,
                Permission\Permissions\Inventory\Brand\BrandUpdatePermission::class,
                Permission\Permissions\Inventory\Brand\BrandDeletePermission::class,
                Permission\Permissions\Inventory\Brand\BrandReadPermission::class,
            ],
            Permission\Permissions\Inventory\FeatureValue\FeatureValuePermissionsGroup::class => [
                Permission\Permissions\Inventory\FeatureValue\FeatureValueCreatePermission::class,
                Permission\Permissions\Inventory\FeatureValue\FeatureValueUpdatePermission::class,
                Permission\Permissions\Inventory\FeatureValue\FeatureValueDeletePermission::class,
                Permission\Permissions\Inventory\FeatureValue\FeatureValueReadPermission::class,
            ],
            Permission\Permissions\Inventory\Feature\FeaturePermissionsGroup::class => [
                Permission\Permissions\Inventory\Feature\FeatureCreatePermission::class,
                Permission\Permissions\Inventory\Feature\FeatureUpdatePermission::class,
                Permission\Permissions\Inventory\Feature\FeatureDeletePermission::class,
                Permission\Permissions\Inventory\Feature\FeatureReadPermission::class,
            ],
            Permission\Permissions\Inventory\Unit\UnitPermissionsGroup::class => [
                Permission\Permissions\Inventory\Unit\UnitCreatePermission::class,
                Permission\Permissions\Inventory\Unit\UnitUpdatePermission::class,
                Permission\Permissions\Inventory\Unit\UnitDeletePermission::class,
                Permission\Permissions\Inventory\Unit\UnitReadPermission::class,
            ],
            Permission\Permissions\Inventory\Inventory\InventoryPermissionsGroup::class => [
                Permission\Permissions\Inventory\Inventory\InventoryCreatePermission::class,
                Permission\Permissions\Inventory\Inventory\InventoryUpdatePermission::class,
                Permission\Permissions\Inventory\Inventory\InventoryDeletePermission::class,
                Permission\Permissions\Inventory\Inventory\InventoryReadPermission::class,
                Permission\Permissions\Inventory\Inventory\InventoryReportPermission::class,
                Permission\Permissions\Inventory\Inventory\InventoryReadTransactionPermission::class,
                Permission\Permissions\Inventory\Inventory\InventoryCreateTransactionPermission::class,
            ],
        ];
    }
}
