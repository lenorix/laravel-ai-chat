<?php

namespace Lenorix\LaravelAiChat\Ai\Actions;

use Closure;
use MalteKuhr\LaravelGPT\GPTAction;

class GuessAiChatNameAction extends GPTAction
{
    public function __construct(
        protected array $messages,
    ) {}

    public function systemMessage(): ?string
    {
        $messagesJson = json_encode($this->messages, JSON_PRETTY_PRINT);

        return <<<EOT
            Guess a good brief name for the chat based on the following messages:

            ```json
            $messagesJson
            ```

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
}
