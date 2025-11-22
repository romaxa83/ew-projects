<?php

use App\Models\Vehicles\Trailer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Trailer::TABLE, function (Blueprint $table) {
            $table->integer('origin_id')->after('id')->unique()->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Trailer::TABLE, function (Blueprint $table) {
            $table->dropColumn('origin_id');
        });
    }
};

