<?php

namespace Lenorix\LaravelAiChat\Ai\Actions;

use Closure;
use MalteKuhr\LaravelGPT\Shim\GPTActionShim;

class GuessAiChatNameAction extends GPTActionShim
{
    public function systemMessage(): ?string
    {
        return <<<'EOT'
            Guess a good brief name for the chat based on the following messages:

            The name should be a single word or a short phrase (less than 50 chars),
             and it should be relevant to the content of the messages.
            EOT;
    }

    public function function(): Closure
    {
        return function (string $name): mixed {
            return $name;
        };
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:50',
        ];
    }

    public function parameters(): array
    {
        return [
            'name' => [
                'type' => 'string',
                'description' => 'Guessed name for the conversation, max 50 chars.',
            ],
        ];
    }

    public function requiredParameters(): array
    {
        return [
            'name',
        ];
    }
}
