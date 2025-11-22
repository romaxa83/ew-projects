<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoteToSupportRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('support_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('closed_at')->nullable();
            $table->string('closed_by')->nullable();
            $table->boolean('closed_by_support_employee')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('support_requests', function (Blueprint $table) {
            $table->dropColumn('closed_at');
            $table->dropColumn('closed_by');
            $table->dropColumn('closed_by_support_employee');
        });
    }
}
