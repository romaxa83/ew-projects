<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionUserRelationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_user_relations', function (Blueprint $table) {
            $table->unsignedBigInteger('promotion_id');
            $table->foreign('promotion_id')
                ->references('id')
                ->on('promotions')
                ->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->primary(['promotion_id', 'user_id'], 'pk-pur_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_user_relations');
    }
}
