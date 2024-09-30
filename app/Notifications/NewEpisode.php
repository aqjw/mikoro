<?php

namespace App\Notifications;

use App\Models\Episode;
use App\Models\Title;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEpisode extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Title $title,
        public Episode $episode,
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the notification's database type.
     *
     * @return string
     */
    public function databaseType(object $notifiable): string
    {
        return 'new-episode';
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $ep = $this->episode;
        return [
            'title' => $this->title->title,
            'subtitle' => "Вышла {$ep->number} серия в озвучке {$ep->translation->title}",
            'image' => $this->title->poster,
            //
            'title_id' => $this->title->id,
            'episode_id' => $ep->id,
            'translation_id' => $ep->translation->title,
        ];
    }
}
