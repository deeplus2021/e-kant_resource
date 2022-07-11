<?php

namespace App\Notifications;

use App\Mail\StaffRequestedMail;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;


class StaffRequested extends Notification
{
    use Queueable;

    protected $object;
    protected $templateMail;

    public function __construct($object)
    {
        $this->object = $object;
        $templateMail = app()->make(StaffRequestedMail::class, ['object' => $object]);
        $this->templateMail = $templateMail->build();
    }

    public function via($notifiable)
    {
        return [
            //'database',
            //'mail',
            'broadcast',
            //FcmChannel::class,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     * @param  mixed  $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        $timestamp = Carbon::now()->addSecond()->toDateTimeString();
        return new BroadcastMessage(array(
            'notifiable_id' => $notifiable->id,
            'notifiable_type' => get_class($notifiable),
            'data' => $this->object,
            'read_at' => null,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ));
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

    /**
     * Get the array representation of the notification.
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return (array) $this->object;
    }

    public function toFcm($notifiable)
    {
        $title = $this->object->title;
        $body = "body";
        return FcmMessage::create()
            ->setData(['type' => 'type', 'value' => 'value'])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($title)
                ->setBody($body)
            )
            ->setWebpush(\NotificationChannels\Fcm\Resources\WebpushConfig::create());
    }
}