<?php

use App\Models\Admins\Admin;
use App\Models\Users\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterInSupportRequestMessagesHaul677 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('support_request_messages', function (Blueprint $table) {
            $table->dropColumn('is_user_message');
            $table->dropColumn('is_read');
            $table->foreignIdFor(User::class)->nullable()->references('id')->on('users');
            $table->foreignIdFor(Admin::class)->nullable()->references('id')->on('admins');
            $table->json('read')->nullable();
            $table->boolean('is_question')->default(false);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('support_request_messages', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->dropColumn('admin_id');
            $table->dropColumn('is_question');
            $table->dropColumn('read');
            $table->boolean('is_user_message')->default(false);
            $table->boolean('is_read')->default(false);
        });
    }
}
