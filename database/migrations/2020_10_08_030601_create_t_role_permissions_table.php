<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTRolePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_role_permissions', function (Blueprint $table) {
            $table->increments('id')->comment('角色权限关系_id');
            $table->unsignedTinyInteger('staff_role_id')->comment('角色_id');
            $table->unsignedSmallInteger('page_menu_id')->comment('页面_id');

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_ip', 32)->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->string('updated_ip', 32)->nullable();
            $table->timestamps();

            //
            $table->foreign('staff_role_id')->references('id')->on('t_staff_roles')->onDelete('cascade');
            $table->foreign('page_menu_id')->references('id')->on('t_page_menu')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_role_permissions');
    }
}
