<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTFieldTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_field', function (Blueprint $table) {
            $table->id()->comment('現場コード');
            $table->string('name', 128)->comment('現場名');
            $table->string('furigana', 255)->comment('ふりがな');
            $table->string('tel', 16)->nullable()->comment('電話番号');
            $table->string('address', 255)->comment('所在地');
            $table->double('latitude')->comment('緯度');
            $table->double('longitude')->comment('経度');
            $table->smallInteger('s_time')->comment('開始時間');
            $table->smallInteger('e_time')->comment('終了時間');
            $table->boolean('is_active')->default(1)->comment('');

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
        Schema::dropIfExists('t_field');
    }
}
