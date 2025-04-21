<?php

namespace Lenorix\LaravelAiChat\Commands;

use Illuminate\Console\Command;

class LaravelAiChatCommand extends Command
{
    public $signature = 'laravel-ai-chat';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
