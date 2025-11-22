<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceIdToAdminsTable extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->nullable();
            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->index('idx_admins_service_id')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropForeign('idx_admins_service_id');
            $table->dropColumn(['service_id']);
        });
    }
}

