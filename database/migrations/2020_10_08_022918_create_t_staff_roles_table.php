<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTStaffRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_staff_roles', function (Blueprint $table) {
            $table->tinyIncrements('id')->comment('角色_id');
            $table->string('name', 64)->comment('角色名称');
            $table->string('desc', 256)->nullable()->comment('描述');
            $table->boolean('is_active')->default(1)->comment('是否有效 0:无效; 1:有效');

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
        Schema::dropIfExists('t_staff_roles');
    }
}
