<?php

namespace App\Notifications;

use App\Models\Enums\MaterialSubmissionActionType;
use App\Models\MaterialSubmission;
use App\Models\MaterialSubmissionAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaterialSubmissionReviewedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private readonly MaterialSubmission       $materialSubmission,
        private readonly MaterialSubmissionAction $action
    )
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->action->type) {
            MaterialSubmissionActionType::RequestChanges => 'Ваша заявка на публикацию нуждается в доработке',
            MaterialSubmissionActionType::Accept         => 'Ваша заявка на публикацию принята',
            MaterialSubmissionActionType::Reject         => 'Ваша заявка на публикацию отклонена',
        };

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Здравствуйте, ' . $notifiable->username . '!');

        if ($this->action->type === MaterialSubmissionActionType::RequestChanges) {
            $message->line('Ваша заявка "' . $this->materialSubmission->materialState?->localization?->title . '" нуждается в доработке.')
                ->line("Комментарий: «{$this->action->details['message']}»")
                ->line('Вы можете внести изменения и отправить заявку повторно.')
                ->action('Перейти к заявке', url("material-submissions/{$this->materialSubmission->id}"));
        } elseif ($this->action->type === MaterialSubmissionActionType::Accept) {
            $message->line('Поздравляем! Ваша заявка "' . $this->materialSubmission->materialState?->localization?->title . '" была принята.')
                ->line('Спасибо за ваш вклад!');
        } elseif ($this->action->type === MaterialSubmissionActionType::Reject) {
            $message->line('К сожалению, ваша заявка "' . $this->materialSubmission->materialState?->localization?->title . '" была отклонена.')
                ->line("Причина: «{$this->action->details['reason']}»");
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
