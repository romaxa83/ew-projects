<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGroupToTranslatesTable extends Migration
{
    public function up(): void
    {
        Schema::table('translates', function (Blueprint $table) {
            $table->string('group', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('translates', function (Blueprint $table) {
            $table->dropColumn('group');
        });
    }
}
