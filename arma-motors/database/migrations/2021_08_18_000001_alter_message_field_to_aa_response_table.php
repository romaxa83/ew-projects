<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMessageFieldToAaResponseTable extends Migration
{
    public function up(): void
    {
        Schema::table('aa_responses', function (Blueprint $table) {
            $table->text('message')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('aa_responses', function (Blueprint $table) {
            $table->string('message')->nullable()->change();
        });
    }
}
