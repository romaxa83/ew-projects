<?php

use App\Models\Users\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(User::TABLE, function (Blueprint $table) {
            $table->integer('origin_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(User::TABLE, function (Blueprint $table) {
            $table->dropColumn('origin_id');
        });
    }
};
