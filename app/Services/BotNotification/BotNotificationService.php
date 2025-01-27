<?php

namespace App\Services\BotNotification;

use App\Models\MaterialSubmission;
use Telegram\Bot\Laravel\Facades\Telegram;

readonly class BotNotificationService
{
    private ?string $telegramBotToken;
    private ?string $telegramModerationChatId;
    private ?string $telegramModerationChatThreadId;

    public function __construct()
    {
        $this->telegramBotToken = env('TELEGRAM_BOT_TOKEN');
        $this->telegramModerationChatId = env('TELEGRAM_MODERATION_CHAT_ID');
        $this->telegramModerationChatThreadId = env('TELEGRAM_MODERATION_CHAT_THREAD_ID');
    }

    public function notifyNewSubmission(MaterialSubmission $materialSubmission): void
    {
        if ($this->telegramBotToken !== null && $this->telegramModerationChatId !== null) {
            $submitterName = $this->escapeTelegramSpecialChars($materialSubmission->submitter->username);
            $materialSubmissionTitle = $this->escapeTelegramSpecialChars($materialSubmission->materialState->localization->title);
            $materialSubmissionUrl = $this->escapeTelegramSpecialChars(url("material-submissions/$materialSubmission->id"));

            Telegram::setAsyncRequest(true)->sendMessage([
                'chat_id' => $this->telegramModerationChatId,
                'parse_mode' => 'MarkdownV2',
                'message_thread_id' => $this->telegramModerationChatThreadId,
                'text' => <<<TEXT
                    Новая заявка на публикацию\!

                    *Отправитель:* $submitterName
                    *Название:* $materialSubmissionTitle
                    *Ссылка:* $materialSubmissionUrl
                    TEXT
            ]);
        }
    }

    private function escapeTelegramSpecialChars(string $text): string
    {
        $escapedSymbols = ['\\', '_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
        foreach ($escapedSymbols as $symbol) {
            $text = str_replace($symbol, '\\' . $symbol, $text);
        }

        return $text;
    }
}
