<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'tire_sizes',
            static function (Blueprint $table)
            {
                $table->dropForeign('tire_sizes_tire_proportionality_id_foreign');
                $table->renameColumn('tire_proportionality_id', 'tire_width_id');

                $table
                    ->foreign('tire_width_id')
                    ->on('tire_widths')
                    ->references('id')
                    ->cascadeOnDelete();

                $table->renameIndex('tire_sizes_tire_proportionality_id_index', 'tire_sizes_tire_width_id_index');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'tire_sizes',
            static function (Blueprint $table)
            {
                $table->dropForeign('tire_sizes_tire_width_id_foreign');
                $table->renameColumn('tire_width_id', 'tire_proportionality_id');

                $table
                    ->foreign('tire_proportionality_id')
                    ->on('tire_widths')
                    ->references('id')
                    ->cascadeOnDelete();

                $table->renameIndex('tire_sizes_tire_width_id_index', 'tire_sizes_tire_proportionality_id_index');
            }
        );
    }
};
