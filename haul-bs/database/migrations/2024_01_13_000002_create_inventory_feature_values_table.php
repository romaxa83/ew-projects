<?php

use App\Models\Inventories\Features\Feature;
use App\Models\Inventories\Features\Value;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Value::TABLE, function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('feature_id');
            $table->foreign('feature_id')
                ->references('id')
                ->on(Feature::TABLE)
                ->onDelete('cascade')
            ;

            $table->boolean('active')->default(true);
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('position')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Value::TABLE);
    }
};
