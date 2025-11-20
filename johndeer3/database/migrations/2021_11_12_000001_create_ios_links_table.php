<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIosLinksTable extends Migration
{
    public function up(): void
    {
        Schema::create('ios_links', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->unique();
            $table->mediumText('link');
            $table->tinyInteger('status')->default(1);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')
                ->on('users')
                ->references('id')
                ->index('ios_link_user_id')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('ios_links', function (Blueprint $table) {
            $table->dropForeign('ios_link_user_id');
        });
        Schema::dropIfExists('ios_links');
    }
}
