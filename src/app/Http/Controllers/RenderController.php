<?php

namespace App\Http\Controllers;

use App\Models\PersonOfInterest;
use App\Services\API\HenrikAPIService;
use App\Services\TrackedPersonService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use stdClass;

class RenderController extends Controller
{
    public function __construct()
    {
        $this->valorant = new HenrikAPIService();
    }

    public function postMatch($matchID)
    {
        $matchDetails = $this->valorant->matchDetails($matchID)['data'];
        $metaData = $matchDetails['metadata'];
        $teamsData = $matchDetails['teams'];
        $playersData = collect($matchDetails['players']['all_players']);

        $players = [];

        if (TrackedPersonService::active()) {
            $team = collect($matchDetails['players']['all_players'])->filter(fn ($player) => $player['puuid'] === TrackedPersonService::puuid())->first()['team'];

            $people = PersonOfInterest::all();

            foreach ($people as $person) {

                $details = $playersData->filter(fn ($player) => $player['puuid'] === $person->puuid)->first();

                if ($details) {
                    $shots = $details['stats']['headshots'] + $details['stats']['bodyshots'] + $details['stats']['legshots'];

                    $players[] = (object)[
                        'puuid' => $person->puuid,
                        'name' => $person->name,
                        'tag' => $person->tag,
                        'kills' => $details['stats']['kills'],
                        'deaths' => $details['stats']['deaths'],
                        'kd_ratio' => $details['stats']['kills'] / ($details['stats']['deaths'] ?: 1),
                        'average_combat_score' => $details['stats']['score'] / $metaData['rounds_played'],
                        'headshot_percentage' => ($shots ? $details['stats']['headshots'] / $shots : 0) * 100,
                        'agent_name' => $details['character'],
                        'agent_icon' => $details['assets']['agent']['small'],
                    ];
                }
            }


            $abstract = (object)[
                'mode' => $metaData['mode'],
                'start' => (new Carbon($metaData['game_start']))->format('H:i, d M Y'),
                'duration' => $metaData['game_length'],
                'map' => $metaData['map'],
                'server' => $metaData['cluster'],
                'our_score' => $metaData['mode'] === 'Deathmatch' ? '-' : $teamsData[strtolower($team)]['rounds_won'],
                'their_score' => $metaData['mode'] === 'Deathmatch' ? '-' : $teamsData[strtolower($team) === 'red' ? 'blue' : 'red']['rounds_won'],
                'was_win' => $metaData['mode'] === 'Deathmatch' ? null : $teamsData[strtolower($team)]['has_won'],
                'players' => $players
            ];

            return view('post-match', ['match' => $abstract]);
        }
        return view('no-user');
    }
}
