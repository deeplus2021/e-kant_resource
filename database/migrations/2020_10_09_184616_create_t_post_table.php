<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTPostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_post', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("field_id")->comment('現場コード');
            $table->smallInteger("p_week")->nullable()->comment('曜日:1-日　2-月　3-火　4-水　5-木　6-金　7-土');
            $table->date("s_date")->nullable()->comment('日');
            $table->date("e_date")->nullable()->comment('日');

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
        Schema::dropIfExists('t_post');
    }
}
