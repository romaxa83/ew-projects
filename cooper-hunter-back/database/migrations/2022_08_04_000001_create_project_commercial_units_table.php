<?php

use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\CommercialProjectUnit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(CommercialProjectUnit::TABLE, function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->unsignedBigInteger('commercial_project_id');
            $table->foreign('commercial_project_id')
                ->references('id')
                ->on(CommercialProject::TABLE)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('serial_number');
            $table->string('name')->nullable();
            $table->integer('sort')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(CommercialProjectUnit::TABLE);
    }
};


