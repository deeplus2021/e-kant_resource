<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTFieldFileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_field_file', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("field_id")->nullable()->comment('現場コード');
            $table->string("name", 255)->comment('ファイル名');
            $table->string("path", 1024)->comment('ファイル保存場所');

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_ip', 32)->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->string('updated_ip', 32)->nullable();
            $table->timestamps();

            $table->foreign('field_id')->references('id')->on('t_field')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_field_file');
    }
}
