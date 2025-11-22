<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Users\User;
use Illuminate\Support\Facades\DB;

class ChangeStatusColumnInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status_new')->default(User::STATUS_ACTIVE);

        });

        Schema::table('users', function (Blueprint $table) {
            DB::table('users')
                ->where('status', false)
                ->update(['status_new' => User::STATUS_INACTIVE]);
            $table->dropColumn('status');
            $table->renameColumn('status_new', 'status');
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
            $table->boolean('status_new')->default(true);
        });

        Schema::table('users', function (Blueprint $table) {
            DB::table('users')
                ->where('status', User::STATUS_INACTIVE)
                ->update(['status_new' => false]);
            $table->dropColumn('status');
            $table->renameColumn('status_new', 'status');
        });
    }
}
