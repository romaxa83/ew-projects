<?php

use App\Models\Agreement\Agreement;
use App\Models\Order\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Order::TABLE_NAME,
            static function (Blueprint $table) {
                $table->boolean('send_push_process')->default(false);
                $table->boolean('send_push_close')->default(false);
            }
        );
    }

    public function down(): void
    {
        Schema::table(Order::TABLE_NAME,
            static function (Blueprint $table) {
                $table->dropColumn('send_push_process');
                $table->dropColumn('send_push_close');
            }
        );
    }
};

