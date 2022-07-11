<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_staff', function (Blueprint $table) {
            $table->id()->comment('スタッフコード');
            $table->string('code', 64)->comment('社員コード');
            $table->string('name', 64)->comment('名前');
            $table->string('furigana', 64)->nullable()->comment('ふりがな');
            $table->string('email', 128)->unique()->comment('メールアドレス');
            $table->timestamp('email_verified_at')->nullable()->comment('');
            $table->string('password', 255)->comment('パスワード');
            $table->string('tel', 16)->nullable()->comment('電話番号');
            $table->unsignedTinyInteger('staff_role_id')->default(3)->comment('権限');
            //TODO
            $table->json('holiday')->nullable()->comment('原則休日登録日--1:月,2:火,3:水,4:木,5:金,6:土,7:日,8:祝日');
            //TODO
            $table->json('desired_holiday')->nullable()->comment('希望休日');
            $table->boolean('yesterday_flag')->default(1)->comment('前日確認');
            $table->boolean('today_flag')->default(1)->comment('当日確認');
            $table->string('fcm_token', 512)->nullable()->comment('fcm token');
            $table->boolean('is_active')->default(1);
            $table->rememberToken();

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_ip', 32)->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->string('updated_ip', 32)->nullable();
            $table->timestamps();

            $table->foreign('staff_role_id')->references('id')->on('t_staff_roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_staff');
    }
}
