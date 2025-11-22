<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAaResponseTable extends Migration
{
    public function up(): void
    {
        Schema::create('aa_responses', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('entity_type', 350)->nullable();
            $table->integer('entity_id')->nullable();
            $table->string('type')->nullable();
            $table->string('model')->nullable();

            $table->string('url');
            $table->string('message')->nullable();
            $table->string('status');
            $table->json('data');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aa_responses');
    }
}

