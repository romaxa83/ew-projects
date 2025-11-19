<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Wezom\Quotes\Models\Quote;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table(Quote::TABLE, function (Blueprint $table) {

            $table->dropColumn('calc_price');

            $table->decimal('mileage_cost', 10, 2)->default(0);
            $table->decimal('cargo_cost', 10, 2)->default(0);
            $table->decimal('storage_cost', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table(Quote::TABLE, function (Blueprint $table) {

            $table->decimal('calc_price', 10, 2)->default(0);

            $table->dropColumn([
                'mileage_cost',
                'cargo_cost',
                'storage_cost',
                'total',
            ]);
        });
    }
};
