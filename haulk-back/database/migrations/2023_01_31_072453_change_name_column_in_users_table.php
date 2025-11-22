<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeNameColumnInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            DB::table('users')
                ->update([
                    'first_name' => DB::raw("split_part(full_name, ' ', 1)"),
                    'last_name' => DB::raw("split_part(full_name, concat(split_part(full_name, ' ', 1), ' '), 2)"),
                ]);

            DB::table('users')
                ->where('last_name', '')
                ->orWhereNull('last_name')
                ->update([
                    'last_name' => 'Last Name',
                ]);

            $table->string('first_name')->nullable(false)->change();
            $table->string('last_name')->nullable(false)->change();
            $table->dropColumn('full_name');
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
            $table->string('full_name')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            DB::table('users')
                ->update([
                    'full_name' => DB::raw("concat(first_name, ' ', last_name)")
                ]);

            $table->string('first_name')->nullable()->change();
            $table->string('last_name')->nullable()->change();
        });
    }
}
