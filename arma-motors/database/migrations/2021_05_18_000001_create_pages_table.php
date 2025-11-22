<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagesTable extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('alias', 50)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
}
