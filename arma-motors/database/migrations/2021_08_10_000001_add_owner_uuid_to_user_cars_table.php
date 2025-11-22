<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOwnerUuidToUserCarsTable extends Migration
{
    public function up(): void
    {
        Schema::table('user_cars', function (Blueprint $table) {
            $table->string('owner_uuid', 50)->index()->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('user_cars', function (Blueprint $table) {
            $table->dropIndex('user_cars_owner_uuid_index');
            $table->dropColumn('owner_uuid');
        });
    }
}
