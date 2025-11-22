<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'distributors',
            static function (Blueprint $table) {
                $table->unsignedInteger('state_id')->after('id');
                $table->foreign('state_id')
                    ->on('states')
                    ->references('id')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'distributors',
            static function (Blueprint $table) {
                $table->dropForeign(['state_id']);
                $table->dropColumn('state_id');
            }
        );
    }
};
