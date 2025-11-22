<?php

use App\Models\Agreement\Agreement;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Agreement::TABLE,
            static function (Blueprint $table) {
                $table->string('dealership_alias')->nullable();
                $table->string('base_order_uuid')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(Agreement::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('dealership_alias');
                $table->dropColumn('base_order_uuid');
            }
        );
    }
};
