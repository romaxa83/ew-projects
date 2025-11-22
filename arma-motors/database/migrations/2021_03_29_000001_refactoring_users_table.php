<?php

use App\Models\User\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefactoringUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email', 50)->nullable()->change();
            $table->boolean('email_verify')->after('email')->default(false);
            $table->string('phone')->unique();
            $table->boolean('phone_verify')->default(false);
            $table->tinyInteger('status')->default(User::DRAFT);
            $table->string('lang')->nullable();
            $table->foreign('lang')
                ->on('languages')
                ->references('slug')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->string('egrpoy', 50)->nullable();
            $table->string('device_id')->nullable();
            $table->string('fcm_token')->nullable();
            $table->dropColumn('email_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email', 50)->change();
            $table->dropColumn('email_verify');
            $table->dropColumn('phone');
            $table->dropColumn('phone_verify');
            $table->dropColumn('status');
            $table->dropForeign(['lang']);
            $table->dropColumn('lang');
            $table->dropColumn('egrpoy');
            $table->dropColumn('device_id');
            $table->dropColumn('fcm_token');
            $table->timestamp('email_verified_at')->nullable();
        });
    }
}
