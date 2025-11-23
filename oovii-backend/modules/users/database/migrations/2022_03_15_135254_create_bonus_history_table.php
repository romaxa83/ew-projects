<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Users\Enums\BonusHistoryType;

class CreateBonusHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'bonus_history',
            function (Blueprint $table) {
                $table->id();
                $table->timestamps();
                $table->foreignId('inviter_id')
                    ->constrained('users')
                    ->cascadeOnDelete();
                $table->foreignId('order_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();
                $table->foreignId('referral_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->enum('type', BonusHistoryType::getValues());
                $table->integer('bonus')->default(0);
                $table->integer('bonus_count')->nullable();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('bonus_history');
    }
}
