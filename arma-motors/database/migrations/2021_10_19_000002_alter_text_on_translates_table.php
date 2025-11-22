<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTextOnTranslatesTable extends Migration
{
    public function up(): void
    {
        Schema::table('translates', function (Blueprint $table) {
            $table->text('text')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('translates', function (Blueprint $table) {
            $table->string('text')->nullable()->change();
        });
    }
}
