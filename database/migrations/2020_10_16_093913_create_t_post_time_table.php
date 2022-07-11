<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTPostTimeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_post_time', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("post_id")->comment('');
            $table->smallInteger("s_time")->comment('開始時間');
            $table->smallInteger("e_time")->comment('終了時間');
            $table->smallInteger("number")->default(0)->comment('人数');

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_ip', 32)->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->string('updated_ip', 32)->nullable();
            $table->timestamps();

            $table->foreign('post_id')->references('id')->on('t_post')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_post_time');
    }
}
