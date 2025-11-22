<?php

use App\Models\TypeOfWorks\TypeOfWork;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(TypeOfWork::TABLE, function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('duration');
            $table->double('hourly_rate');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(TypeOfWork::TABLE);
    }
};
