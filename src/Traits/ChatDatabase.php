<?php

namespace Lenorix\LaravelAiChat\Traits;

use Lenorix\LaravelAiChat\Models\AiChat;
use Lenorix\LaravelAiChat\Models\AiChatMessage;
use MalteKuhr\LaravelGPT\Enums\ChatRole;
use MalteKuhr\LaravelGPT\Models\ChatMessage;

trait ChatDatabase
{
    abstract protected function getAiChatFromDatabase(): AiChat;

    public function loadChatFromDatabase($maxMessages = 200): void
    {
        $this->messages = $this->getAiChatFromDatabase()
            ->chatMessages($maxMessages)
            ->get()
            ->map(function (AiChatMessage $message) {
                $role = ChatRole::tryFrom($message->role);

                return new ChatMessage($role, content: $message->content);
            })
            ->toArray();
    }

    public function saveMessageToDatabase(ChatMessage|string $message): AiChatMessage
    {
        return $this->getAiChatFromDatabase()->addMessage($message);
    }
}
