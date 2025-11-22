<?php

use App\Models\Inventories\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Category::TABLE, function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')
                ->references('id')
                ->on(Category::TABLE);

            $table->boolean('active')->default(true);
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('desc', 1000)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->integer('origin_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Category::TABLE);
    }
};
