<?php

namespace App\Contracts\Payment;

use Illuminate\Contracts\View\View;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\Payment;

interface PaymentWithDataInterface
{
    /**
     * @return array|string[]
     */
    public function siteFields(): array;

    public function siteRules(): array;

    public function saveSiteData(Order $order, Payment $payment, array $data, ?int $companyId = null);

    public function renderAdminForm(?Order $order): ?View;

    public function adminRules(): array;

    /**
     * Create or update storage.
     */
    public function saveAdminData(Order $order, array $data): void;
}
