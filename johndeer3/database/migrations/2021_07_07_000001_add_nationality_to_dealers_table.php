<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNationalityToDealersTable extends Migration
{
    public function up(): void
    {
        Schema::table('jd_dealers', function (Blueprint $table) {
            $table->unsignedBigInteger('nationality_id')->nullable();
            $table->foreign('nationality_id')
                ->references('id')
                ->on('nationalities')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('jd_dealers', function (Blueprint $table) {
            $table->dropForeign('jd_dealers_nationality_id_foreign');
            $table->dropIndex('jd_dealers_nationality_id_foreign');
            $table->dropColumn('nationality_id');
        });
    }
}
