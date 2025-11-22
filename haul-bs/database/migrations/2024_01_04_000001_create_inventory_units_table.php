<?php

use App\Models\Inventories\Unit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Unit::TABLE, function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('accept_decimals');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Unit::TABLE);
    }
};
