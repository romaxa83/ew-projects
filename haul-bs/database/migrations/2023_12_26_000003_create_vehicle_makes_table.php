<?php

use App\Models\Vehicles\Make;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Make::TABLE, function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->timestamp('last_updated');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Make::TABLE);
    }
};
