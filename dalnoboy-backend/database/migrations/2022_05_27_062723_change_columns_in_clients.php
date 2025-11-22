<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'clients',
            static function (Blueprint $table)
            {
                $table
                    ->string('inn', 10)
                    ->nullable()
                    ->unique()
                    ->after('edrpou');

                $table
                    ->string('edrpou', 10)
                    ->change();

                $table
                    ->boolean('active')
                    ->after('show_ban_in_inspection')
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
            'clients',
            static function (Blueprint $table)
            {
                $table->dropColumn('inn');
                $table
                    ->string('edrpou')
                    ->change();
                $table->dropColumn('active');
                $table->dropColumn('is_moderated');
            }
        );
    }
};
