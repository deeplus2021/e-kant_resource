<?php

namespace App\Notifications;

use App\Mail\AdminNotifiedMail;
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

class AdminStarted extends Notification
{
    use Queueable;

    protected $type;
    protected $content;
    protected $shift;
    protected $templateMail;

    public function __construct(int $type, Shift $shift)
    {
        $this->type = $type;
        $this->shift = $shift;
        $this->content = array(
            "title" => "勤務通知",
            "body" => "出発の時間です！"
        );
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

        return FcmMessage::create()
            ->setData([
                'type' => "{$this->type}",
                'shift_id' => "{$this->shift->id}",
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
                            ->setSound("default")
                    )
            )->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('analytics_ios'))
                    ->setPayload(['aps' => ['sound' => 'default']])
            );
    }
}
