<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent_id')->unsigned()
                ->index()->nullable();
            $table->foreign('parent_id')->references('id')
                ->on('menus');
            $table->string('name')->nullable();
            $table->string('display_name')->nullable();
            $table->string('icon')->nullable();
            $table->integer('order')->nullable();
            $table->string('url')->nullable();
            $table->boolean('hide')->default(false);
            $table->boolean('has_children')->default(false);
            $table->string('position', 50)->default('sidebar_left');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menus');
    }
}
