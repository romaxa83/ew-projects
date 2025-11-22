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
                $table->timestamp('invoice_at')
                    ->after('invoice')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(Order::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('invoice_at');
            }
        );
    }
};
