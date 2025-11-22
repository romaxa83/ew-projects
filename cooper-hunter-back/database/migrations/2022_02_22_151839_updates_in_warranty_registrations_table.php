<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'warranty_registrations',
            static function (Blueprint $table) {
                $table->string('member_type')->nullable()->change();
                $table->unsignedBigInteger('member_id')->nullable()->change();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'warranty_registrations',
            static function (Blueprint $table) {
                $table->string('member_type')->nullable(false)->change();
                $table->unsignedBigInteger('member_id')->nullable(false)->change();
            }
        );
    }
};
