<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCanCheckOrdersIntoUsersTable extends Migration
{
    public function up(): void
    {
        Schema::table(
            'users',
            function (Blueprint $table) {
                $table->boolean('can_check_orders')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'users',
            function (Blueprint $table) {
                $table->dropColumn('can_check_orders');
            }
        );
    }
}
