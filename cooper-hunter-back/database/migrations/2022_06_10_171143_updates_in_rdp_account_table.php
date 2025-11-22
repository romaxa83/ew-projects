<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'rdp_accounts',
            static function (Blueprint $table) {
                $table->boolean('created_on_ad')->default(false)->after('id');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'rdp_accounts',
            static function (Blueprint $table) {
                $table->dropColumn('created_on_ad');
            }
        );
    }
};
