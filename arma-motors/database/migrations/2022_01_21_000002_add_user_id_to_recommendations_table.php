<?php

use App\Models\Recommendation\Recommendation;
use App\Models\User\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Recommendation::TABLE,
            static function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->after("id");
                $table->foreign('user_id')
                    ->references('id')
                    ->on(User::TABLE_NAME)
                    ->index('idx_recommendation-user_id')
                    ->onDelete('cascade');
            }
        );
    }

    public function down(): void
    {
        Schema::table(Recommendation::TABLE,
            static function (Blueprint $table) {
                $table->dropForeign('idx_recommendation-user_id');
                $table->dropColumn(['user_id']);
            }
        );
    }
};
