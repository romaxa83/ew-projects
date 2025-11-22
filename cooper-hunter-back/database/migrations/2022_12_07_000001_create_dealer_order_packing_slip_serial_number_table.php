<?php

use App\Models\Catalog\Products\Product;
use App\Models\Orders\Dealer\PackingSlip;
use App\Models\Orders\Dealer\PackingSlipSerialNumber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(PackingSlipSerialNumber::TABLE,
            static function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('packing_slip_id');
                $table->foreign('packing_slip_id')
                    ->on(PackingSlip::TABLE)
                    ->references('id')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->unsignedBigInteger('product_id');
                $table->foreign('product_id')
                    ->on(Product::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('serial_number')->nullable();
                $table->unique(['product_id', 'serial_number'],'product_id-serial_number-ps-uniq');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(PackingSlipSerialNumber::TABLE);
    }
};
