<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTPageMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_page_menu', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->primary();
            $table->unsignedSmallInteger('parent_id');
            $table->smallInteger('order')->nullable();
            $table->string('code', 32)->nullable();
            $table->string('name', 32)->nullable();
            $table->string('url', 256)->nullable();
            $table->string('button_type', 1)->nullable();
            $table->string('image_url', 256)->nullable();
            $table->string('desc', 256)->nullable();
            $table->tinyInteger('version')->default(1);

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_ip', 32)->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->string('updated_ip', 32)->nullable();
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
        Schema::dropIfExists('t_page_menu');
    }
}
