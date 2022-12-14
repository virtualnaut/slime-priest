<?php

namespace App\Services\API;


class HenrikAPIService extends AbstractAPIService
{
    /**
     * Get all of player's info by name and tag.
     */
    public function playerInfo(string $name, string $tag)
    {
        $response = $this->get("https://api.henrikdev.xyz/valorant/v1/account/$name/$tag");
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
        $response = $this->post('https://api.henrikdev.xyz/valorant/v1/raw', [
            'type' => 'matchhistory',
            'value' => $puuid,
            'region' => 'eu',
            'queries' => "?endIndex=$n"
        ]);
        return $response->json();
    }

    public function lastMatch(string $puuid)
    {
        return $this->matchHistory($puuid, 1)['History'][0]['MatchID'];
    }

    /**
     * Get the details for a match.
     */
    public function matchDetails(string $matchId)
    {
        $response = $this->get("https://api.henrikdev.xyz/valorant/v2/match/$matchId");
        return $response->json();
    }
}
