<?php

namespace App\Services\Discord;

use Illuminate\Support\Facades\Http;

class BotService
{
    public function sendTo($channelID, $message)
    {
        Http::post('slimebot:5000/send', [
            'channel_id' => $channelID,
            'message' => $message
        ]);
    }

    public function status()
    {
        $response = Http::get('slimebot:5000/status');
        return $response->json();
    }
}
