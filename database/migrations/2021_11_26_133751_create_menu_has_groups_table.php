<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuHasGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('menu_has_groups', function (Blueprint $table) {
        //     $table->unsignedBigInteger('menu_id');
        //     $table->unsignedBigInteger('menu_group_id');

        //     $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
        //     $table->foreign('menu_group_id')->references('id')->on('menu_groups')->onDelete('cascade');

        //     $table->primary(['menu_id']);
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('menu_has_groups');
    }
}
