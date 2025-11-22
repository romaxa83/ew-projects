<?php

use App\Models\Catalog\Products\Product;
use App\Models\Warranty\Deleted\WarrantyRegistrationDeleted;
use App\Models\Warranty\Deleted\WarrantyRegistrationUnitPivotDeleted;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(WarrantyRegistrationUnitPivotDeleted::TABLE,
            static function (Blueprint $table) {
                $table->unsignedBigInteger('warranty_registration_deleted_id');
                $table->foreign('warranty_registration_deleted_id', 'warranty_registration_deleted_id_foreign')
                    ->on(WarrantyRegistrationDeleted::TABLE)
                    ->references('id')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->unsignedBigInteger('product_id');
                $table->foreign('product_id')
                    ->on(Product::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('serial_number');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(WarrantyRegistrationUnitPivotDeleted::TABLE);
    }
};

