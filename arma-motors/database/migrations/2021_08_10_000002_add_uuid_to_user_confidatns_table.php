<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUuidToUserConfidatnsTable extends Migration
{
    public function up(): void
    {
        Schema::table('user_car_confidants', function (Blueprint $table) {
            $table->string('uuid', 50)->after('id')->index()->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('user_car_confidants', function (Blueprint $table) {
            $table->dropIndex('user_car_confidants_uuid_index');
            $table->dropColumn('uuid');
        });
    }
}
