<?php

use App\Models\Companies\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('lang')->nullable();

            $table->foreign('lang')
                ->on('languages')
                ->references('slug')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });

        Company::query()->update(['lang' => app('localization')->getDefaultSlug()]);
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['lang']);
            $table->dropColumn('lang');
        });
    }
};
