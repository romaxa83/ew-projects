<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new Exception(
                'Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.'
            );
        }

        Schema::table($tableNames['roles'], function (Blueprint $table) {
            $table->string('title')->nullable();
            $table->string('system_type')->nullable();
            $table->string('note')->nullable();
            $table->boolean('active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new Exception('Error: config/permission.php not found and defaults could not be merged.' .
                ' Please publish the package configuration before proceeding, or drop the tables manually.');
        }

        Schema::table($tableNames['roles'], function (Blueprint $table) {
            $table->dropColumn(['system_type', 'note', 'active']);
        });
    }
};
