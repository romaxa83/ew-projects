<?php

use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\CommercialProjectAddition;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(CommercialProjectAddition::TABLE, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('commercial_project_id');
            $table->foreign('commercial_project_id')
                ->references('id')
                ->on(CommercialProject::TABLE)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->timestamp('purchase_date')->nullable();
            $table->timestamp('installation_date')->nullable();
            $table->string('installer_license_number')->nullable();
            $table->string('purchase_place')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(CommercialProjectAddition::TABLE);
    }
};

