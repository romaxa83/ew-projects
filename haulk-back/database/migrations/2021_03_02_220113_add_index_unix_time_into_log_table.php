<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexUnixTimeIntoLogTable extends Migration
{

    public function up(): void
    {
        Schema::table(
            'log',
            function (Blueprint $table) {
                $table->index(['unix_time']);
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'log',
            function (Blueprint $table) {
                $table->dropIndex(['unix_time']);
            }
        );
    }
}
