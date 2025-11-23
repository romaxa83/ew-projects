<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Firebase\Models\Template;
use WezomCms\Firebase\Models\TemplateTranslation;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(TemplateTranslation::TABLE,
            static function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('template_id');
                $table->foreign('template_id')
                    ->references('id')
                    ->on(Template::TABLE)
                    ->onDelete('cascade');

                $table->string('locale')->index();
                $table->string('title');
                $table->string('text', 2000);

                $table->unique(['template_id', 'locale']);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(TemplateTranslation::TABLE);
    }
};
