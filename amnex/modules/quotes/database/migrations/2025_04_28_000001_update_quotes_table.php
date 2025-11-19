<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Wezom\Quotes\Models\Quote;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table(Quote::TABLE, function (Blueprint $table) {
            $table->boolean('is_palletized')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table(Quote::TABLE, function (Blueprint $table) {
            $table->boolean('is_palletized')->nullable(false)->change();
        });
    }
};

