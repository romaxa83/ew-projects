<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'tickets',
            static function (Blueprint $table) {
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');

                $table->string('serial_number')->index();

                $table->jsonb('order_parts')->after('guid');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'tickets',
            static function (Blueprint $table) {
                $table->dropColumn('order_parts');

                $table->dropIndex(['serial_number']);
                $table->dropColumn('serial_number');

                $table->foreignId('product_id')
                    ->after('id')
                    ->constrained('catalog_products')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }
};
