<?php

namespace LakM\Comments\Notifications\Guest;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyLinkGenerated extends Notification
{
    public function __construct(private string $link)
    {
    }

    public function via()
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->greeting('Hello!')
            ->line('Please verify your email to comment.')
            ->action('Verify', $this->link)
            ->line('Thank you for using our application!');
    }
}
