<?php

namespace Lenorix\LaravelAiChat;

use Lenorix\LaravelAiChat\Commands\LaravelAiChatCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelAiChatServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-ai-chat')
            /*->hasConfigFile()
            ->hasViews()*/
            ->hasMigration('create_laravel_ai_chat_table')
            /*->hasCommand(LaravelAiChatCommand::class)*/;
    }
}
