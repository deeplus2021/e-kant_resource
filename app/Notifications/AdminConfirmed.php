<?php

namespace App\Notifications;

use App\Mail\AdminConfirmedMail;
use App\Models\Shift;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidFcmOptions;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\ApnsConfig;
use NotificationChannels\Fcm\Resources\ApnsFcmOptions;

class AdminConfirmed extends Notification
{
    use Queueable;

    protected $type;
    protected $content;
    protected $shift;
    protected $check_type;
    protected $templateMail;
    protected $check_type_str = ["拒否","承認", "承認(変更有)"];

    public function __construct(int $type, int $check_type, Shift $shift, $contents)
    {
        $this->type = $type;
        $this->shift = $shift;
        $this->check_type = $check_type;
        $type_txt = ["", "", "", "早退", "休日", "残業", "振替日", "遅刻", "始業時間", "終業時間", "休憩時間", "深夜休憩時間"];
        $reply = $this->check_type==0 ? "再申請してください。" : "";
        $this->content = array(
            "title" => "勤怠承認",
            "body" => $type_txt[$type]."申請が{$this->check_type_str[$check_type]}されました。{$reply}",
            "new_value" => $contents["new_value"],
            "old_value" => $contents["old_value"],
        );
        $templateMail = app()->make(AdminConfirmedMail::class, ['content'=>$this->content,'object' => $shift]);
        $this->templateMail = $templateMail->build();
    }

    public function via($notifiable)
    {
        return [
            FcmChannel::class,
            //'mail',
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return $this->templateMail->to($notifiable->email);
    }

    public function toFcm($notifiable)
    {
        $title = $this->content["title"];
        $body = $this->content["body"];

        $field = $this->shift->field;

        $emergency_tel = "";
        $estaff = $this->shift->field->estaffs()->first();
        if(isset($estaff)){
            $emergency_tel = $estaff->tel;
        }
        $shift_time = \Carbon\Carbon::parse($this->shift->shift_date)->addMinutes($this->shift->s_time);
        return FcmMessage::create()
            ->setData([
                'type' => "{$this->type}",
                'check_type' => "{$this->check_type}",
                'shift_id' => "{$this->shift->id}",
                'field_name' => "{$field->name}",
                'field_tel' => "{$field->tel}",
                'admin_tel' => "{$notifiable->tel}",
                'emergency_tel' => "{$emergency_tel}",
                'shift_time' => "{$shift_time}",
                "new_value" => "{$this->content["new_value"]}",
                "old_value" => "{$this->content["old_value"]}"
            ])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($title)
                ->setBody($body)
            )
            ->setAndroid(
                AndroidConfig::create()
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('analytics'))
                    ->setNotification(
                        AndroidNotification::create()
                            ->setColor('#0A0A0A')
                            ->setClickAction('MainActivity')
                    )
            )->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('analytics_ios'))
            );
    }
}
