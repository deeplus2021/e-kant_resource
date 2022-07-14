<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStaffAddressIdToTShiftTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('t_shift', function (Blueprint $table) {
            $table->unsignedBigInteger("staff_address_id")->nullable()->comment('t_staff_address id');

            $table->foreign('staff_address_id')->references('id')->on('t_staff_address')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('t_shift', function (Blueprint $table) {
            $table->dropForeign(['staff_address_id']);
            $table->dropColumn('staff_address_id');
        });
    }
}
