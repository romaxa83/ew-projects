<?php

use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Order::TABLE, function (Blueprint $table) {
            $table->boolean('first_calc_as_client')
                ->default(false)
            ;
        });
    }

    public function down(): void
    {
        Schema::table(Order::TABLE, function (Blueprint $table) {
            $table->dropColumn('first_calc_as_client');
        });
    }
};
