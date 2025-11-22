<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::rename('tire_proportionalities', 'tire_widths');

        Schema::table(
            'tire_widths',
            static function (Blueprint $table)
            {
                $table->renameIndex('tire_proportionalities_order_column_index', 'tire_widths_order_column_index');
            }
        );
    }

    public function down(): void
    {
        Schema::rename('tire_widths', 'tire_proportionalities');

        Schema::table(
            'tire_widths',
            static function (Blueprint $table)
            {
                $table->renameIndex('tire_widths_order_column_index', 'tire_proportionalities_order_column_index');
            }
        );
    }
};
