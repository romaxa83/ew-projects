<?php

use Illuminate\Database\Migrations\Migration;
use WezomCms\Firebase\Models\Template;

class SeedFcmTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Artisan::call('cmd:set-template');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table(Template::TABLE)->delete();
    }
}
