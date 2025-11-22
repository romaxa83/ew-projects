<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'rdp_credentials',
            static function (Blueprint $table) {
                $table->foreignId('rdp_account_id')
                    ->after('credentials_request_id')
                    ->constrained()
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'rdp_credentials',
            static function (Blueprint $table) {
                $table->dropForeign(['rdp_account_id']);

                $table->dropColumn('rdp_account_id');
            }
        );
    }
};
