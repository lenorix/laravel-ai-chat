<?php

namespace Lenorix\LaravelAiChat\Traits;

use Lenorix\LaravelAiChat\Models\AiChat;
use Lenorix\LaravelAiChat\Models\AiChatMessage;
use MalteKuhr\LaravelGPT\Concerns\HasChat;
use MalteKuhr\LaravelGPT\Enums\ChatRole;
use MalteKuhr\LaravelGPT\Models\ChatMessage;

trait ChatDatabase
{
    use HasChat;

    abstract protected function getAiChat(): AiChat;

    public function loadChat(?int $maxLatestMessages = null): static
    {
        $this->messages = $this->getAiChat()
            ->chatMessages($maxLatestMessages ?: 200)
            ->get()
            ->map(function (AiChatMessage $message) {
                $role = ChatRole::tryFrom($message->role);

                return new ChatMessage($role, content: $message->content);
            })
            ->toArray();

        return $this;
    }

    public function addMessage(ChatMessage|string $message): static
    {
        // This always must do same than HasChat::addMessage()
        if (is_string($message)) {
            $message = ChatMessage::from(
                role: ChatRole::USER,
                content: $message
            );
        }

        $this->messages[] = $message;
        // End of the same as HasChat::addMessage()

        $this->getAiChat()->addMessage($message);

        return $this;
    }

    public function saveChat(): static
    {
        return $this;
    }
}
