<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class SimpleNotification extends Notification
{

    protected $title;
    protected $message;
    protected $actionUrl;

    
    public function __construct(string $title, string $message, ?string $actionUrl = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->actionUrl = $actionUrl;
    }

    
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'action_url' => $this->actionUrl,
        ];
    }
}
