<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTStaffAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_staff_address', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("staff_id")->comment('スタッフコード');
            $table->string('address', 255)->comment('出発先住所');
            $table->double('latitude')->comment('緯度');
            $table->double('longitude')->comment('経度');
            $table->unsignedBigInteger("field_id")->comment('現場コード');
            $table->smallInteger("required_time")->comment("現場までの時間");
            $table->smallInteger("email_time")->comment("当日確認メール送信時間");

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_ip', 32)->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->string('updated_ip', 32)->nullable();
            $table->timestamps();

            $table->foreign('field_id')->references('id')->on('t_field')->onDelete('cascade');
            $table->foreign('staff_id')->references('id')->on('t_staff')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_staff_address');
    }
}
