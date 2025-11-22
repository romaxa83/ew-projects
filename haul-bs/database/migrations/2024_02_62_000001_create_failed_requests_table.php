<?php

use App\Foundations\Modules\Request\Models\FailRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(FailRequest::TABLE, function (Blueprint $table) {
            $table->id();

            $table->nullableNumericMorphs('model');
            $table->string('type', 30);
            $table->string('reason', 600)->nullable();
            $table->json('data');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(FailRequest::TABLE);
    }
};
