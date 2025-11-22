<?php

use App\Models\Order\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Order::TABLE_NAME,
            static function (Blueprint $table) {
                $table->string('type')->default(Order::TYPE_ORDINARY);
            }
        );
    }

    public function down(): void
    {
        Schema::table(Order::TABLE_NAME,
            static function (Blueprint $table) {
                $table->dropColumn('type');
            }
        );
    }
};
