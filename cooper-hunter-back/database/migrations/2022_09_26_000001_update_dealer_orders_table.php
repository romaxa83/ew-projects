<?php

use App\Models\Orders\Dealer\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Order::TABLE,
            static function (Blueprint $table) {
                $table->unsignedInteger('total_discount')->default(0);
                $table->unsignedInteger('total_with_discount')->default(0);
                $table->string('invoice')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(Order::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('total_discount');
                $table->dropColumn('total_with_discount');
                $table->dropColumn('invoice');
            }
        );
    }
};


