<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFeatureTypeToFeaturesTable extends Migration
{
    public function up(): void
    {
        Schema::table('reports_features', function (Blueprint $table) {
            $table->string('type_feature', 20)
                ->after('type_field')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reports_features', function (Blueprint $table) {
            $table->dropColumn('type_feature');
        });
    }
}
