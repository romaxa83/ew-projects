<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParsingGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'parsing_groups',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->timestamps();
                $table->string('name');

                $table->unsignedBigInteger('type_id');
                $table->foreign('type_id', 'parsing_groups_type_id_parsing_types_id')
                    ->on('parsing_types')
                    ->references('id')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->unsignedBigInteger('parent_id');
                $table->foreign('parent_id', 'parsing_groups_parent_id_parsing_groups_id')
                    ->on('parsing_groups')
                    ->references('id')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parsing_groups');
    }
}
