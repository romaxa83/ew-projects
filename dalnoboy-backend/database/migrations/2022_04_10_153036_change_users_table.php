<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'users',
            static function (Blueprint $table)
            {
                $table->dropColumn('first_name');
                $table->dropColumn('last_name');
                $table->dropColumn('middle_name');
            }
        );
        Schema::table(
            'users',
            static function (Blueprint $table)
            {
                $table->string('first_name')
                    ->after('phone');
                $table->string('last_name')
                    ->after('first_name');
                $table->string('second_name')
                    ->after('last_name')
                    ->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'users',
            static function (Blueprint $table)
            {
                $table->dropColumn('first_name');
                $table->dropColumn('last_name');
                $table->dropColumn('second_name');
            }
        );
        Schema::table(
            'users',
            static function (Blueprint $table)
            {
                $table->string('first_name')
                    ->after('email_verification_code');
                $table->string('last_name')
                    ->after('first_name');
                $table->string('middle_name')
                    ->after('last_name');
            }
        );
    }
};
