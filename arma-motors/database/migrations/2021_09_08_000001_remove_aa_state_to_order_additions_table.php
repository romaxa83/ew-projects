<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveAaStateToOrderAdditionsTable extends Migration
{
    public function up(): void
    {
        Schema::table('order_additions', function (Blueprint $table) {
            $table->dropColumn('aa_state');
            $table->dropColumn('aa_state_payment');
        });
    }

    public function down(): void
    {
        Schema::table('order_additions', function (Blueprint $table) {
            $table->tinyInteger('aa_state')->default(0);
            $table->tinyInteger('aa_state_payment')->default(0);
        });
    }
}
