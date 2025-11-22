<?php

use App\Foundations\Modules\Location\Models\City;
use App\Foundations\Modules\Location\Models\State;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(City::TABLE, function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('zip', 20);
            $table->boolean('active')->default(true);

            $table->foreignId('state_id')
                ->references('id')
                ->on(State::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('timezone', 50)->nullable();
            $table->string('country_code', 20);
            $table->string('country_name', 20);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(City::TABLE);
    }
};
