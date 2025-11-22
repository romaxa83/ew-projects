<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGroupIdToPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id');
            $table->foreign('group_id')
                ->references('id')
                ->on('permission_groups')
                ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign('permissions_group_id_foreign');
            $table->dropColumn('group_id');
        });
    }
}

