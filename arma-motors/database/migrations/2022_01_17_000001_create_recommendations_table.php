<?php

use App\Models\Recommendation\Recommendation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Recommendation::TABLE,
            static function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->tinyInteger('status')->default(Recommendation::STATUS_NEW);
                $table->string('uuid')->nullable();
                $table->string('car_uuid');
                $table->string('order_uuid')->nullable();
                $table->string('qty')->nullable();
                $table->mediumText('text');
                $table->mediumText('comment')->nullable();
                $table->mediumText('rejection_reason')->nullable();
                $table->string('author')->nullable();
                $table->string('executor')->nullable();
                $table->boolean('completed')->default(false);
                $table->json('data');
                $table->timestamp('completion_at')->nullable();
                $table->timestamp('relevance_at')->nullable();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Recommendation::TABLE);
    }
};
