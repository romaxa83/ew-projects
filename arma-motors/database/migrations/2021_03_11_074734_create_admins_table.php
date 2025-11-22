<?php

use App\Dto\Admin\AdminDTO;
use App\Models\Admin\Admin;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminsTable extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('lang')->nullable();
            $table->foreign('lang')
                ->on('languages')
                ->references('slug')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropForeign(['lang']);
            $table->dropColumn('lang');
        });
        Schema::dropIfExists('admins');
    }
}

