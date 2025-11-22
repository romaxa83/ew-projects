<?php

use App\Models\Vehicles\Make;
use App\Models\Vehicles\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Model::TABLE, function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->foreignId('make_id')
                ->references('id')
                ->on(Make::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Model::TABLE);
    }
};
