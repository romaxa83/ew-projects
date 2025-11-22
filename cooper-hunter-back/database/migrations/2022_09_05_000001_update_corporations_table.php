<?php

use App\Models\Companies\Corporation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Corporation::TABLE, function (Blueprint $table) {
            $table->string('name', 500)->change();
        }
        );
    }

    public function down(): void
    {
        Schema::table(Corporation::TABLE, function (Blueprint $table) {
            $table->string('name', 4)->change();
        });
    }
};


