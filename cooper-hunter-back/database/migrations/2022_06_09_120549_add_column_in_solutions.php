<?php

use App\Enums\Solutions\SolutionTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'solutions',
            static function (Blueprint $table)
            {
                $table
                    ->unsignedInteger('max_btu_percent')
                    ->nullable()
                    ->after('btu');
            }
        );

        DB::table('solutions')
            ->where('type', SolutionTypeEnum::OUTDOOR)
            ->update(['max_btu_percent' => config('catalog.solutions.btu.max_percent')]);
    }

    public function down(): void
    {
        Schema::table(
            'solutions',
            static function (Blueprint $table)
            {
                $table->dropColumn('max_btu_percent');
            }
        );
    }
};
