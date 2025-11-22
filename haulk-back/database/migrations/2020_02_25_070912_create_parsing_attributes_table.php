<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParsingAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'parsing_attributes',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->timestamps();

                $table->string('name');
                $table->string('type');
                $table->string('pattern', 1024);

                $table->unsignedBigInteger('group_id');
                $table->foreign('group_id', 'parsing_attributes_group_id_parsing_groups_id')
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
        Schema::dropIfExists('parsing_attributes');
    }
}
