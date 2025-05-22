<?php

namespace Lenorix\LaravelAiChat\Traits;

use Lenorix\Ai\Chat\CoreMessage;
use Lenorix\LaravelAiChat\Models\AiChat;
use Lenorix\LaravelAiChat\Models\AiChatMessage;
use MalteKuhr\LaravelGPT\Concerns\HasChat;
use MalteKuhr\LaravelGPT\Enums\ChatRole;
use MalteKuhr\LaravelGPT\Models\ChatMessage;
use MalteKuhr\LaravelGPT\Shim\GPTChatShim;

trait ChatDatabase
{
    use HasChat {
        addMessage as protected traitAddMessage;
    }

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

    public function addMessage(ChatMessage|CoreMessage|string $message): static
    {
        $this->traitAddMessage($message);

        if ($message instanceof ChatMessage) {
            $message = GPTChatShim::migrateMessage($message);
        }

        $this->getAiChat()->addMessage($message);

        return $this;
    }

    public function saveChat(): static
    {
        return $this;
    }
}
