<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIosLinkImportsTable extends Migration
{
    public function up(): void
    {
        Schema::create('ios_link_imports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->on('users')
                ->references('id')
                ->index('ios_link_imports_user_id')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('status')->nullable();
            $table->longText('message')->nullable();
            $table->longText('error_data')->nullable();
            $table->string('file')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('ios_link_imports', function (Blueprint $table) {
            $table->dropForeign('ios_link_imports_user_id');
        });
        Schema::dropIfExists('ios_link_imports');
    }
}
