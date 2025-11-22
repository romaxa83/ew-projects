<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'technicians',
            static function (Blueprint $table) {
                $table->after(
                    'is_certified',
                    static function (Blueprint $table) {
                        $table->unsignedInteger('state_id');
                        $table->string('hvac_license')->nullable();
                        $table->string('epa_license')->nullable();

                        $table->foreign('state_id')
                            ->references('id')
                            ->on('states')
                            ->cascadeOnDelete()
                            ->cascadeOnUpdate();
                    }
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'technicians',
            static function (Blueprint $table) {
                $table->dropForeign(['state_id']);
                $table->dropColumn(['hvac_license', 'epa_license', 'state_id']);
            }
        );
    }
};
