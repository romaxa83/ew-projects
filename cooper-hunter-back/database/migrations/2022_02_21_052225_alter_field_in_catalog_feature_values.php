<?php

use App\Models\Catalog\Features\Metric;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'catalog_feature_values',
            static function (Blueprint $table)
            {
                $table->unsignedBigInteger('metric_id')
                    ->after('feature_id')
                    ->nullable();

                $table->foreign('metric_id', 'metric_id_foreign')
                    ->on(Metric::TABLE)
                    ->references('id')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'catalog_feature_values',
            static function (Blueprint $table)
            {
                $table->dropForeign('metric_id_foreign');
                $table->dropColumn('metric_id');
            }
        );
    }
};
