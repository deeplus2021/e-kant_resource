<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTStaffStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_staff_status', function (Blueprint $table) {
            $table->tinyIncrements("id");
            $table->string("name", 255)->comment('スタッフの状況');
            $table->tinyInteger("sort")->default(0)->comment('');
            $table->string("desc", 255)->nullable()->comment('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_staff_status');
    }
}
