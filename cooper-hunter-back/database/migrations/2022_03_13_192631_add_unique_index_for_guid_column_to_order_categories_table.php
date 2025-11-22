<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'order_categories',
            static function (Blueprint $table) {
                $table->unique('guid', 'order_categories_guid_unique');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'order_categories',
            static function (Blueprint $table) {
                $table->dropIndex('order_categories_guid_unique');
            }
        );
    }
};
