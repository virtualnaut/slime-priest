<?php

namespace App\Services\Discord;

use App\Services\API\HenrikAPIService;
use App\Services\TrackedPersonService;
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
        $matchDetails = $this->valorant->matchDetails($matchID)['data'];

        $team = collect($matchDetails['players']['all_players'])->filter(fn ($player) => $player['puuid'] === TrackedPersonService::puuid())->first()['team'];

        Http::post("{$this->baseURL}/send/embed/post-match", [
            'channel_id' => $channelID,
            'message' => [
                'mode' => $matchDetails['metadata']['mode'],
                'start' => $matchDetails['metadata']['game_start'],
                'duration' => $matchDetails['metadata']['game_length'],
                'map' => $matchDetails['metadata']['map'],
                'server' => $matchDetails['metadata']['cluster'],
                'our_score' => $matchDetails['teams'][strtolower($team)]['rounds_won'],
                'their_score' => $matchDetails['teams'][strtolower($team) === 'red' ? 'blue' : 'red']['rounds_won']
            ]
        ]);
    }

    public function status()
    {
        $response = Http::get('slimebot:5000/status');
        return $response->json();
    }
}
