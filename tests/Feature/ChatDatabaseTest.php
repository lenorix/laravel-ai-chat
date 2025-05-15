<?php

use Lenorix\LaravelAiChat\Models\AiChat;
use MalteKuhr\LaravelGPT\GPTChat;

it('save messages', function () {
    $dbChat = AiChat::create();

    $aiChat = new class extends GPTChat
    {
        use Lenorix\LaravelAiChat\Traits\ChatDatabase;

        protected function getAiChat(): AiChat
        {
            return AiChat::first();
        }
    };

    $aiChat->addMessage('Hello');
    expect($dbChat->messages()->count())->toBe(1)
        ->and($dbChat->messages()->first()->content)->toBe('Hello');
    $dbChat->delete();
});

it('load messages', function () {
    $dbChat = AiChat::create();
    $dbChat->addMessage('Hello');
    $dbChat->addMessage('This');
    $dbChat->addMessage('World');

    $aiChat = new class extends GPTChat
    {
        use Lenorix\LaravelAiChat\Traits\ChatDatabase;

        protected function getAiChat(): AiChat
        {
            return AiChat::first();
        }
    };

    expect($aiChat->messages)->toBeEmpty();
    $aiChat->loadChat(2);

    expect($aiChat->latestMessage()->content)->toBe('World');
    $dbChat->delete();
});
