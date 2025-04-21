<?php

namespace Lenorix\LaravelAiChat\Traits;

use Lenorix\LaravelAiChat\Models\AiChat;
use Lenorix\LaravelAiChat\Models\AiChatMessage;
use MalteKuhr\LaravelGPT\Enums\ChatRole;
use MalteKuhr\LaravelGPT\Models\ChatMessage;

trait ChatDatabase
{
    abstract protected function getAiChatFromDatabase(): AiChat;

    public function loadChatFromDatabase($totalMessages = 200): void
    {
        $this->messages = $this->getAiChatFromDatabase()
            ->messages()
            ->latest('created_at')
            ->latest('id')
            ->whereIn('role', [ChatRole::USER->value, ChatRole::ASSISTANT->value])
            ->take($totalMessages)
            ->get()
            ->map(function (AiChatMessage $message) {
                $role = ChatRole::tryFrom($message->role);

                return new ChatMessage($role, content: $message->content);
            })
            ->reverse()
            ->toArray();
    }

    public function saveMessageToDatabase(ChatMessage|string $message): AiChatMessage
    {
        return $this->getAiChatFromDatabase()->addMessage($message);
    }
}
