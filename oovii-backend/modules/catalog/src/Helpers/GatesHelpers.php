<?php

namespace WezomCms\Catalog\Helpers;

use Closure;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Models\Administrator;
use WezomCms\Imports\Models\Import;
use WezomCms\Orders\Models\Order;
use WezomCms\ProductReviews\Models\ProductReview;

class GatesHelpers
{
    public static function modelGate(string $permission): Closure
    {
        return static function (Administrator $admin, $model = null) use ($permission): bool
        {
            $adminHasPermission = $admin->hasAccess($permission);
            $modelPermission = true;

            if ($adminHasPermission) {
                $modelPermission = match (true) {
                    $model instanceof Product => self::productGate($admin, $model),
                    $model instanceof ProductReview => self::productReviewGate($admin, $model),
                    $model instanceof Order => self::orderGate($admin, $model),
                    $model instanceof Import => self::importGate($admin, $model),
                    default => true,
                };
            }

            return $adminHasPermission && $modelPermission;
        };
    }

    public static function productModerateGate(): Closure
    {
        return static function (Administrator $admin, Product $product): bool
        {
            $adminHasPermission = $admin->hasAccess('products.moderate');
            $modelPermission = true;

            if (!$admin->isSuperAdmin()) {
                $modelPermission = $product->moderator_id === $admin->id;
            }

            return $adminHasPermission && $modelPermission;
        };
    }

    public static function productGate(Administrator $admin, Product $product): bool
    {
        if ($admin->onlyProvider()) {
            return $product->provider_id === $admin->id;
        }

        return true;
    }

    public static function productReviewGate(Administrator $admin, ProductReview $review): bool
    {
        if ($admin->onlyProvider()) {
            return $review->product && $review->product->provider_id === $admin->id;
        }

        return true;
    }

    public static function orderGate(Administrator $admin, Order $order): bool
    {
        if ($admin->onlyProvider()) {
            return $order->provider && $order->provider->admin_id === $admin->id;
        }

        return true;
    }

    public static function importGate(Administrator $admin, Import $import): bool
    {
        if ($admin->onlyProvider()) {
            return $import->administrator_id === $admin->id;
        }

        return true;
    }
}
