<?php

namespace Lenorix\LaravelAiChat\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Lenorix\LaravelAiChat\LaravelAiChat
 */
class LaravelAiChat extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Lenorix\LaravelAiChat\LaravelAiChat::class;
    }
}
