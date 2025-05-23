<?php

namespace Lenorix\LaravelAiChat\Traits;

use Lenorix\Ai\Chat\CoreMessage;
use Lenorix\Ai\Chat\CoreMessageRole;
use Lenorix\LaravelAiChat\Models\AiChat;
use Lenorix\LaravelAiChat\Models\AiChatMessage;
use MalteKuhr\LaravelGPT\Concerns\HasChatShim;
use MalteKuhr\LaravelGPT\Models\ChatMessage;
use MalteKuhr\LaravelGPT\Shim\GPTChatShim;

trait ChatDatabase
{
    use HasChatShim {
        addMessage as protected traitAddMessage;
    }

    abstract protected function getAiChat(): AiChat;

    public function loadChat(?int $maxLatestMessages = null): static
    {
        $this->messages = $this->getAiChat()
            ->chatMessages($maxLatestMessages ?: 200)
            ->get()
            ->map(function (AiChatMessage $message) {
                $role = CoreMessageRole::from($message->role);

                return new CoreMessage(
                    role: $role,
                    content: $message->content,
                    toolCalls: $message->tool_calls,
                    toolCallId: $message->tool_call_id
                );
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
