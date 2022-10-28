<?php

namespace App\Services\API;

use Illuminate\Support\Facades\Http;

class HenrikAPIService
{
    /**
     * Get all of player's info by name and tag.
     */
    public function playerInfo(string $name, string $tag)
    {
        $response = Http::get("https://api.henrikdev.xyz/valorant/v1/account/$name/$tag");
        return $response->json();
    }

    /**
     * Get the PUUID for a player by name and tag.
     */
    public function puuid(string $name, string $tag)
    {
        return $this->playerInfo($name, $tag)['data']['puuid'];
    }

    /**
     * Get the IDs of the previous N matches.
     */
    public function matchHistory(string $puuid, int $n)
    {
        $response = Http::post('https://api.henrikdev.xyz/valorant/v1/raw', [
            'type' => 'matchhistory',
            'value' => $puuid,
            'region' => 'eu',
            'queries' => "?endIndex=$n"
        ]);
        return $response->json();
    }

    /**
     * Get the details for a match.
     */
    public function matchDetails(string $matchId)
    {
        $response = Http::get("https://api.henrikdev.xyz/valorant/v2/match/$matchId");
        return $response->json();
    }
}
