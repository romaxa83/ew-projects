<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Wezom\Quotes\Models\Quote;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table(Quote::TABLE, function (Blueprint $table) {
            $table->string('container_type', 20);
            $table->boolean('is_not_standard_dimension');
            $table->boolean('is_transload');
            $table->boolean('is_palletized');
            $table->integer('number_pallets')->nullable();
            $table->integer('piece_pallets')->nullable();
            $table->integer('days_stored')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->timestamp('quote_accepted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Quote::TABLE, function (Blueprint $table) {
            $table->dropColumn('container_type');
            $table->dropColumn('is_not_standard_dimension');
            $table->dropColumn('is_transload');
            $table->dropColumn('is_palletized');
            $table->dropColumn('number_pallets');
            $table->dropColumn('piece_pallets');
            $table->dropColumn('days_stored');
            $table->dropColumn('email');
            $table->dropColumn('phone');
            $table->dropColumn('quote_accepted_at');
        });
    }
};
