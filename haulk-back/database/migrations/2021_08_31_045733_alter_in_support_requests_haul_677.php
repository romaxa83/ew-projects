<?php

use App\Models\Saas\Support\SupportRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterInSupportRequestsHaul677 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('support_requests', function (Blueprint $table) {
            $table->dropColumn('carrier_id');
            $table->json('viewed')->nullable();
            $table->smallInteger('source')->default(SupportRequest::SOURCE_CARRIER);
            $table->smallInteger('label')->nullable()->change();
        });

        Schema::dropIfExists('support_requests_viewers');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('support_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('carrier_id')
                ->nullable();
            $table->foreign('carrier_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->dropColumn('viewed');
            $table->dropColumn('source');
            $table->smallInteger('label')->change();
        });

        Schema::create('support_requests_viewers', function (Blueprint $table) {
            $table->bigInteger('support_request_id');
            $table->bigInteger('admin_id');
            $table->foreign('support_request_id')
                ->references('id')
                ->on('support_requests')
                ->onDelete('cascade');
            $table->foreign('admin_id')
                ->references('id')
                ->on('admins');
            $table->timestamps();
        });
    }
}
