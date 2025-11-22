<?php

use App\Models\Settings\Settings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Settings::TABLE, function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->index();
            $table->text('value')->nullable('true');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Settings::TABLE);
    }
};
