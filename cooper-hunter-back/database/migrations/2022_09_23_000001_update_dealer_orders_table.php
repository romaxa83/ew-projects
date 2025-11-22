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
                $table->json('terms')->nullable()->change();
                $table->json('files')->nullable();
                $table->unsignedInteger('tax')->default(0);
                $table->unsignedInteger('shipping_price')->default(0);
                $table->unsignedInteger('total')->default(0);
            }
        );
    }

    public function down(): void
    {
        Schema::table(Order::TABLE,
            static function (Blueprint $table) {
                $table->string('terms')->nullable()->change();
                $table->dropColumn('files');
                $table->dropColumn('tax');
                $table->dropColumn('shipping_price');
                $table->dropColumn('total');
            }
        );
    }
};

