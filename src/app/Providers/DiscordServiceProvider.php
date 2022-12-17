<?php

namespace App\Providers;

use App\Services\Discord\BotService;
use Illuminate\Support\ServiceProvider;

class DiscordServiceProvider extends ServiceProvider
{
    public $singletons = [
        BotService::class
    ];
}
