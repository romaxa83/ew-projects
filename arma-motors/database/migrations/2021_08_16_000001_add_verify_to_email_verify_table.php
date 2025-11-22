<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVerifyToEmailVerifyTable extends Migration
{
    public function up(): void
    {
        Schema::table('email_verify', function (Blueprint $table) {
            $table->boolean('verify')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('email_verify', function (Blueprint $table) {
            $table->dropColumn('verify');
        });
    }
}

