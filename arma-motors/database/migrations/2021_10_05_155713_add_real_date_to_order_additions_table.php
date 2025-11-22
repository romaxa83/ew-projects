<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRealDateToOrderAdditionsTable extends Migration
{
    public function up(): void
    {
        Schema::table('order_additions', function (Blueprint $table) {
            $table->timestamp('real_date')->nullable()->after('on_date');
            $table->timestamp('for_current_filter_date')->nullable()->after('real_date');
        });
    }

    public function down(): void
    {
        Schema::table('order_additions', function (Blueprint $table) {
            $table->dropColumn('real_date');
            $table->dropColumn('for_current_filter_date');
        });
    }
}
