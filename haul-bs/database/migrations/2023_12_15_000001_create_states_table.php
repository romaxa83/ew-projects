<?php

use App\Foundations\Modules\Location\Models\State;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(State::TABLE, function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('active')->default(false);
            $table->string('state_short_name', 10);
            $table->string('country_code', 10);
            $table->string('country_name');

            $table->index('name');
            $table->unique(['name', 'country_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(State::TABLE);
    }
};
