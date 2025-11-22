<?php

use App\Models\Order\Additions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Additions::TABLE_NAME,
            static function (Blueprint $table) {
                $table->unsignedBigInteger('recommendation_id')->nullable();
                $table->foreign('recommendation_id')
                    ->references('id')
                    ->on('recommendations')
                    ->index('idx_order-addition_recommendation_id')
                    ->onDelete('cascade');
            }
        );
    }

    public function down(): void
    {
        Schema::table(Additions::TABLE_NAME,
            static function (Blueprint $table) {
                $table->dropForeign('idx_order-addition_recommendation_id');
                $table->dropColumn(['recommendation_id']);
            }
        );
    }
};
