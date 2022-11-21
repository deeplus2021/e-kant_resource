<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTShiftTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_shift', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("staff_id")->comment('スタッフコード');
            $table->unsignedBigInteger("alter_id")->nullable()->comment('代行シフト');
            $table->unsignedBigInteger("field_id")->comment('現場コード');

            $table->unsignedBigInteger("admin_id")->nullable()->comment('承認者');
            $table->timestamp("confirmed_at")->nullable()->comment("承認確認");

            $table->date("shift_date")->comment("日付");

            $table->smallInteger("field_s_time")->nullable()->comment("出社時間");
            $table->smallInteger("field_e_time")->nullable()->comment("退社時間");

            $table->smallInteger("s_time")->nullable()->comment("シフト出社時間");
            $table->smallInteger("e_time")->nullable()->comment("シフト退社時間");

            $table->smallInteger("ks_time")->nullable()->comment("休憩時間");
            $table->smallInteger("ke_time")->nullable()->comment("休憩時間");

            $table->smallInteger("sks_time")->nullable()->comment("深夜休憩時間");
            $table->smallInteger("ske_time")->nullable()->comment("深夜休憩時間");

            $table->unsignedTinyInteger("staff_status_id")->nullable()->comment("staff status");

            $table->boolean("yesterday_no_check")->nullable()->comment("前日確認");
            $table->timestamp("yesterday_checked_at")->nullable()->comment("前日確認");

            $table->boolean("today_no_check")->nullable()->comment("当日確認");
            $table->timestamp("today_checked_at")->nullable()->comment("当日確認");
            $table->boolean("health_status")->nullable()->comment("健康状态");

            $table->timestamp("start_check_at")->nullable()->comment("出発確認");
            $table->timestamp("start_checked_at")->nullable()->comment("出発確認");

            $table->timestamp("arrive_checked_at")->nullable()->comment("到着確認");
            $table->timestamp("leave_checked_at")->nullable()->comment("退勤済");

            $table->timestamp("break_at")->nullable()->comment("休憩時間");
            $table->smallInteger("break_time")->nullable()->comment("休憩時間");

            $table->timestamp("night_break_at")->nullable()->comment("深夜休憩時間");
            $table->smallInteger("night_break_time")->nullable()->comment("深夜休憩時間");


            $table->smallInteger("e_leave_time")->nullable()->comment("早退時間");
            $table->timestamp("e_leave_at")->nullable()->comment("早退申請時間");
            $table->timestamp("e_leave_checked_at")->nullable()->comment("早退確認時間");

            $table->timestamp("rest_at")->nullable()->comment('休日申請');
            $table->timestamp("rest_checked_at")->nullable()->comment('休日承認');

            $table->smallInteger("over_time")->nullable()->comment("残業時間");
            $table->timestamp("over_time_at")->nullable()->comment("残業申請時間");
            $table->timestamp("over_time_checked_at")->nullable()->comment('残業承認');

            $table->date("alt_date")->nullable()->comment("振替日");
            $table->timestamp("alt_date_at")->nullable()->comment("振替日申請時間");
            $table->timestamp("alt_date_checked_at")->nullable()->comment('振替日承認');

            $table->smallInteger('late_time')->nullable()->comment("遅刻");
            $table->timestamp('late_at')->nullable()->comment("遅刻");
            $table->timestamp('late_checked_at')->nullable()->comment("遅刻");

            $table->timestamp("arrive_changed_at")->nullable()->comment("");
            $table->timestamp("arrive_changed_checked_at")->nullable()->comment("");
            $table->timestamp("leave_changed_at")->nullable()->comment("");
            $table->timestamp("leave_changed_checked_at")->nullable()->comment("");
            $table->timestamp("break_changed_at")->nullable()->comment("");
            $table->smallInteger("break_changed_time")->nullable()->comment("");
            $table->timestamp("break_changed_checked_at")->nullable()->comment("");
            $table->timestamp("night_break_changed_at")->nullable()->comment("");
            $table->smallInteger("night_break_changed_time")->nullable()->comment("");
            $table->timestamp("night_break_changed_checked_at")->nullable()->comment("");

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_ip', 32)->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->string('updated_ip', 32)->nullable();
            $table->timestamps();

            $table->foreign('staff_id')->references('id')->on('t_staff')->onDelete('cascade');
            $table->foreign('field_id')->references('id')->on('t_field')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('t_staff');
            $table->foreign('staff_status_id')->references('id')->on('t_staff_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_shift');
    }
}
