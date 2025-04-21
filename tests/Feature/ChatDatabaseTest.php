<?php

it('save messages', function () {
    $dbChat = \Lenorix\LaravelAiChat\Models\AiChat::create();

    $aiChat = new class() {
        use MalteKuhr\LaravelGPT\Concerns\HasChat;
        use Lenorix\LaravelAiChat\Traits\ChatDatabase;

        protected function getAiChatFromDatabase(): \Lenorix\LaravelAiChat\Models\AiChat
        {
            return \Lenorix\LaravelAiChat\Models\AiChat::first();
        }
    };

    $aiChat->saveMessageToDatabase('Hello');
    expect($dbChat->messages()->count())->toBe(1);
    expect($dbChat->messages()->first()->content)->toBe('Hello');
});

it('load messages', function () {
    $dbChat = \Lenorix\LaravelAiChat\Models\AiChat::create();
    $dbChat->addMessage('Hello');
    $dbChat->addMessage('This');
    $dbChat->addMessage('World');

    $aiChat = new class() {
        use MalteKuhr\LaravelGPT\Concerns\HasChat;
        use Lenorix\LaravelAiChat\Traits\ChatDatabase;

        protected function getAiChatFromDatabase(): \Lenorix\LaravelAiChat\Models\AiChat
        {
            return \Lenorix\LaravelAiChat\Models\AiChat::first();
        }
    };

    expect($aiChat->messages)->toBeEmpty();
    $aiChat->loadChatFromDatabase(2);
    expect($aiChat->latestMessage()->content)->toBe('World');
});
