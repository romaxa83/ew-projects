<?php

use App\Models\AA\AAOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(AAOrder::TABLE,
            static function (Blueprint $table) {
                $table->dropUnique('aa_orders_order_uuid_unique');
                $table->boolean("is_sys")->default(true);
            }
        );
    }

    public function down(): void
    {
        Schema::table(AAOrder::TABLE,
            static function (Blueprint $table) {
                $table->string('order_uuid')->unique()->nullable()->change();
                $table->dropColumn('is_sys');
            }
        );
    }
};

