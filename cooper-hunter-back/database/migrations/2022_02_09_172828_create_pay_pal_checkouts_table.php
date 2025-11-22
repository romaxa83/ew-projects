<?php

use App\Enums\Payments\PaymentReturnPlatformEnum;
use App\Enums\Payments\PayPalCheckoutStatusEnum;
use App\Enums\Payments\PayPalRefundStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'pay_pal_checkouts',
            static function (Blueprint $table)
            {
                $table->string('id')
                    ->primary()
                    ->unique();

                $table->string('capture_id')
                    ->unique()
                    ->nullable();

                $table->string('refund_id')
                    ->unique()
                    ->nullable();

                $table->unsignedBigInteger('order_id');
                $table->unsignedInteger('amount');

                $table->enum('return_platform', PaymentReturnPlatformEnum::getValues())
                    ->default(PaymentReturnPlatformEnum::WEB);
                $table->enum('checkout_status', PayPalCheckoutStatusEnum::getValues());
                $table->enum('refund_status', PayPalRefundStatusEnum::getValues())
                    ->nullable();

                $table->text('approve_url');
                $table->unsignedBigInteger('created_at');

                $table->foreign('order_id')
                    ->on('orders')
                    ->references('id')
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('pay_pal_checkouts');
    }
};
