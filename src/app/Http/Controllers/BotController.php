<?php

namespace App\Http\Controllers;

use App\Services\Discord\BotService;

class BotController extends Controller
{
    protected $bot;

    public function __construct()
    {
        $this->bot = new BotService();
    }

    public function resend()
    {
        $this->bot->sendLastMatchSummary(env('DISCORD_OUTPUT_CHANNEL_ID'));
    }
}
