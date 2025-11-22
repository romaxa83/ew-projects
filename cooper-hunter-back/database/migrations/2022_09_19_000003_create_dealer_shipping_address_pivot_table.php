<?php

use App\Models\Companies\ShippingAddress;
use App\Models\Dealers\Dealer;
use App\Models\Dealers\DealerShippingAddressPivot;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(DealerShippingAddressPivot::TABLE,
            static function (Blueprint $table) {
                $table->unsignedBigInteger('dealer_id');
                $table->foreign('dealer_id')
                    ->on(Dealer::TABLE)
                    ->references('id')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->unsignedBigInteger('shipping_address_id');
                $table->foreign('shipping_address_id')
                    ->on(ShippingAddress::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(DealerShippingAddressPivot::TABLE);
    }
};
