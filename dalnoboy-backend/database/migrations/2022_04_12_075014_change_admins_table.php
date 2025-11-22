<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'admins',
            static function (Blueprint $table)
            {
                $table->dropColumn('name');
                $table->string('first_name')
                    ->after('id');
                $table->string('last_name')
                    ->after('first_name');
                $table->string('second_name')
                    ->after('last_name')
                    ->nullable();

                $table->string('phone')
                    ->after('email');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'admins',
            static function (Blueprint $table)
            {
                $table->dropColumn('phone');
                $table->dropColumn('first_name');
                $table->dropColumn('last_name');
                $table->dropColumn('second_name');
                $table->string('name')
                    ->after('id');
            }
        );
    }
};
