<?php

it('save messages', function () {
    $dbChat = \Lenorix\LaravelAiChat\Models\AiChat::create();

    $aiChat = new class()
    {
        use Lenorix\LaravelAiChat\Traits\ChatDatabase;
        use MalteKuhr\LaravelGPT\Concerns\HasChat;

        protected function getAiChatFromDatabase(): \Lenorix\LaravelAiChat\Models\AiChat
        {
            return \Lenorix\LaravelAiChat\Models\AiChat::first();
        }
    };

    $aiChat->saveMessageToDatabase('Hello');
    expect($dbChat->messages()->count())->toBe(1);
    expect($dbChat->messages()->first()->content)->toBe('Hello');
    $dbChat->delete();
});

it('load messages', function () {
    $dbChat = \Lenorix\LaravelAiChat\Models\AiChat::create();
    $dbChat->addMessage('Hello');
    $dbChat->addMessage('This');
    $dbChat->addMessage('World');

    $aiChat = new class()
    {
        use Lenorix\LaravelAiChat\Traits\ChatDatabase;
        use MalteKuhr\LaravelGPT\Concerns\HasChat;

        protected function getAiChatFromDatabase(): \Lenorix\LaravelAiChat\Models\AiChat
        {
            return \Lenorix\LaravelAiChat\Models\AiChat::first();
        }
    };

    expect($aiChat->messages)->toBeEmpty();
    $aiChat->loadChatFromDatabase(2);
    expect($aiChat->latestMessage()->content)->toBe('World');
    $dbChat->delete();
});

it('can create system prompt from chat', function () {
    $aiChat = new class()
    {
        use MalteKuhr\LaravelGPT\Concerns\HasChat;
    };

    $aiChat->addMessage('Hello');
    $systemPrompt = \Lenorix\LaravelAiChat\Ai\Actions\GuessAiChatNameAction::make($aiChat->messages)
        ->systemMessage();

    expect($systemPrompt)->toContain('```json');
});

it('can create system prompt from database chat', function () {
    $dbChat = \Lenorix\LaravelAiChat\Models\AiChat::create();
    $dbChat->addMessage('Hello');

    $messages = $dbChat->messages()->get()->toArray();
    $systemPrompt = \Lenorix\LaravelAiChat\Ai\Actions\GuessAiChatNameAction::make($messages)
        ->systemMessage();

    expect($systemPrompt)->toContain('```json');
    $dbChat->delete();
});
