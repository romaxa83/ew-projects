<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatalogSeoTemplateParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalog_seo_template_parameters', function (Blueprint $table) {
            $table->foreignId('catalog_seo_template_id')->constrained()->cascadeOnDelete();
            $table->string('parameter');

            $table->primary(
                ['catalog_seo_template_id', 'parameter'],
                'catalog_seo_tpl_params_catalog_seo_tpl_id_param_primary'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalog_seo_template_parameters');
    }
}
