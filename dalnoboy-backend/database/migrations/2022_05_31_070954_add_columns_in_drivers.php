<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'drivers',
            static function (Blueprint $table)
            {
                $table
                    ->boolean('active')
                    ->after('client_id')
                    ->default(true);

                $table
                    ->boolean('is_moderated')
                    ->after('active')
                    ->default(true);
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'drivers',
            static function (Blueprint $table)
            {
                $table->dropColumn('active');
                $table->dropColumn('is_moderated');
            }
        );
    }
};
