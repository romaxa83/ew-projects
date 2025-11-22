<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'global_settings',
            static function (Blueprint $table) {
                $table->dropColumn('footer_description');
                $table->string('footer_additional_email');
                $table->string('footer_app_store_link');
                $table->string('footer_google_pay_link');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'global_settings',
            static function (Blueprint $table) {
                $table->string('footer_description');
                $table->dropColumn('footer_additional_email');
                $table->dropColumn('footer_app_store_link');
                $table->dropColumn('footer_google_pay_link');
            }
        );
    }
};
