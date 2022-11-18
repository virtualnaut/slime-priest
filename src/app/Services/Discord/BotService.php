<?php

namespace App\Services\Discord;

use App\Services\API\HenrikAPIService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class BotService
{
    const URL = 'slimebot';
    const PORT = '5000';

    public function __construct()
    {
        $this->baseURL = self::URL . ':' . self::PORT;
        $this->valorant = new HenrikAPIService();
    }

    public function sendTo($channelID, $message)
    {
        Http::post("{$this->baseURL}/send", [
            'channel_id' => $channelID,
            'message' => $message
        ]);
    }

    public function sendPostMatchSummary($channelID, $matchID)
    {
        Http::post("{$this->baseURL}/send/html", [
            'channel_id' => $channelID,
            'message' => [
                'loading_message' => 'Match ended, loading summary...',
                'url' => "slimeweb/render/post-match/$matchID",
                'filename' => 'summary',
                'format' => 'png'
            ]
        ]);
    }

    public function sendLastMatchSummary($channelID)
    {
        $this->sendPostMatchSummary($channelID, Cache::get('last-match-id'));
    }

    public function status()
    {
        $response = Http::get('slimebot:5000/status');
        return $response->json();
    }
}
