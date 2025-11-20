<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserEgRelationTable extends Migration
{
    public function up(): void
    {
        Schema::create('user_eg_relation', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->unsignedBigInteger('eg_id');
            $table->foreign('eg_id')
                ->references('id')
                ->on('jd_equipment_groups')
                ->onDelete('cascade');

            $table->primary(['user_id', 'eg_id'], 'pk-uer_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_eg_relation');
    }
}

